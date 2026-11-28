<?php

namespace App\Http\Controllers;

use App\Realtime\ConcertChannel;
use App\Realtime\RealtimeSocket;
use App\Services\TicketStore;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Padanan server.js guru: subscribe tabel concerts, lalu teruskan ke browser lewat
 * Server-Sent Events. Karena proses PHP tidak hidup terus seperti Node, tiap koneksi
 * /events subscribe sendiri, ditutup berkala, dan disambung lagi oleh EventSource.
 * Jadi cukup web server saja, tanpa proses pendengar terpisah.
 */
class EventsController extends Controller
{
    public function __invoke(ConcertChannel $channel, RealtimeSocket $socket, TicketStore $store): StreamedResponse
    {
        // Harus di bawah batas diam Supabase (sekitar 60 detik) karena tanpa heartbeat.
        $seconds = min((int) config('services.supabase.events_seconds'), 50);

        return response()->stream(function () use ($channel, $socket, $store, $seconds): void {
            $this->write("retry: 1000\n\n");

            try {
                $this->forward($channel, $socket, $store, microtime(true) + $seconds);
            } catch (Throwable $exception) {
                // Pesan bisa memuat URL berisi key, jadi key disensor.
                $key = (string) config('services.supabase.key');
                Log::warning('Koneksi realtime gagal: '.str_replace($key ?: '[key]', '[key]', $exception->getMessage()));
                // Jangan menyambung ulang terlalu cepat saat Supabase bermasalah.
                $this->write("retry: 10000\n\n");
            } finally {
                $socket->close();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function forward(ConcertChannel $channel, RealtimeSocket $socket, TicketStore $store, float $stopAt): void
    {
        $url = config('services.supabase.url');
        $key = config('services.supabase.key');

        if (blank($url) || blank($key)) {
            throw new RuntimeException('SUPABASE_URL / SUPABASE_KEY belum diisi.');
        }

        $socket->connect($channel->url($url, $key));
        $socket->send($channel->joinMessage());

        $stocks = null;

        do {
            $message = $socket->receive();

            switch ($channel->handle($message ?? [])) {
                case 'subscribed':
                    // Ambil data terkini setelah subscribe, supaya perubahan saat
                    // menyambung ulang tidak hilang. Versinya sama kalau datanya sama.
                    $concerts = $store->concerts();
                    $stocks = $concerts->pluck('available_tickets', 'id')->map(fn ($n) => (int) $n)->all();
                    $this->send(md5($concerts->toJson()), $stocks, snapshot: true);
                    break;
                case 'changed':
                    if ($stocks !== null) {
                        $stocks = $channel->apply($stocks, $message);
                        // Selalu versi baru, walaupun sisa tiketnya sama (judul diedit,
                        // atau pesanan dihapus lalu trigger menyentuh konsernya).
                        $this->send((string) Str::ulid(), $stocks);
                    }
                    break;
                case 'failed':
                    throw new RuntimeException('Channel ditolak: '.json_encode($message['payload'] ?? []));
                default:
                    // Komentar SSE: menjaga koneksi dan mendeteksi tab yang sudah ditutup.
                    $this->write(": ping\n\n");
            }
        } while (! connection_aborted() && microtime(true) < $stopAt);
    }

    /**
     * @param  array<int, int>  $stocks
     */
    private function send(string $version, array $stocks, bool $snapshot = false): void
    {
        $this->write('data: '.json_encode([
            'version' => $version,
            'snapshot' => $snapshot,
            'concerts' => (object) $stocks,
        ])."\n\n");
    }

    private function write(string $chunk): void
    {
        echo $chunk;

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
