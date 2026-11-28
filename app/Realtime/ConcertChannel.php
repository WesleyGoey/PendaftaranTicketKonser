<?php

namespace App\Realtime;

/**
 * Pesan protokol Supabase Realtime untuk channel tabel concerts.
 * Padanan supabase.channel(...).on('postgres_changes', ...).subscribe() di server.js guru.
 */
class ConcertChannel
{
    public const TOPIC = 'realtime:realtime-concerts';

    public function url(string $supabaseUrl, string $key): string
    {
        $base = preg_replace('/^http/', 'ws', rtrim($supabaseUrl, '/'));

        return $base.'/realtime/v1/websocket?'.http_build_query(['apikey' => $key, 'vsn' => '1.0.0']);
    }

    /**
     * @return array<string, mixed>
     */
    public function joinMessage(): array
    {
        return [
            'topic' => self::TOPIC,
            'event' => 'phx_join',
            'payload' => [
                'config' => [
                    'broadcast' => ['self' => false],
                    'presence' => ['key' => ''],
                    'postgres_changes' => [
                        ['event' => '*', 'schema' => 'public', 'table' => 'concerts'],
                    ],
                ],
            ],
            'ref' => '1',
            'join_ref' => '1',
        ];
    }

    /**
     * @param  array<string, mixed>  $message
     * @return string "subscribed", "changed", "failed", atau "ignored"
     */
    public function handle(array $message): string
    {
        if (($message['topic'] ?? null) !== self::TOPIC) {
            return 'ignored';
        }

        $event = $message['event'] ?? '';
        $payload = $message['payload'] ?? [];

        if (in_array($event, ['phx_error', 'phx_close'], true)
            || (in_array($event, ['phx_reply', 'system'], true) && ($payload['status'] ?? null) === 'error')) {
            return 'failed';
        }

        // "system ok" dikirim setelah langganan postgres_changes benar-benar aktif.
        if ($event === 'system' && ($payload['status'] ?? null) === 'ok') {
            return 'subscribed';
        }

        return $event === 'postgres_changes' ? 'changed' : 'ignored';
    }

    /**
     * Terapkan satu perubahan ke daftar sisa tiket.
     *
     * @param  array<int, int>  $stocks  id konser => sisa tiket
     * @param  array<string, mixed>  $message  pesan "changed"
     * @return array<int, int>
     */
    public function apply(array $stocks, array $message): array
    {
        $change = $message['payload']['data'] ?? [];

        if (($change['type'] ?? null) === 'DELETE') {
            unset($stocks[$change['old_record']['id'] ?? null]);
        } elseif (isset($change['record']['id'], $change['record']['available_tickets'])) {
            $stocks[(int) $change['record']['id']] = (int) $change['record']['available_tickets'];
        }

        return $stocks;
    }
}
