<?php

namespace App\Services;

use App\Models\Concert;
use App\Models\TicketPurchase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

/**
 * Data konser dan pesanan lewat REST API Supabase.
 * Model Concert dan TicketPurchase hanya wadah data, tidak dipakai untuk query.
 */
class TicketStore
{
    public function __construct(private SupabaseClient $client) {}

    /**
     * @return Collection<int, Concert>
     */
    public function concerts(): Collection
    {
        $response = $this->send(fn () => $this->client->request()->get('/rest/v1/concerts', [
            'select' => '*',
            'order' => 'concert_date.asc',
        ]));

        return collect($response->json())->map(fn (array $row) => $this->toConcert($row));
    }

    public function concert(int $id): ?Concert
    {
        $response = $this->send(fn () => $this->client->request()->get('/rest/v1/concerts', [
            'select' => '*',
            'id' => 'eq.'.$id,
        ]));

        $row = $response->json('0');

        return $row ? $this->toConcert($row) : null;
    }

    /**
     * Memakai token user, jadi RLS di database hanya mengembalikan pesanannya sendiri.
     *
     * @return Collection<int, TicketPurchase>
     */
    public function purchases(string $accessToken): Collection
    {
        $response = $this->send(fn () => $this->client->request()->withToken($accessToken)->get('/rest/v1/ticket_purchases', [
            'select' => '*,concert:concerts(*)',
            'order' => 'created_at.desc,id.desc',
        ]));

        return collect($response->json())->map(function (array $row) {
            $concert = $row['concert'] ?? [];
            unset($row['concert']);

            return $this->toPurchase($row, $this->toConcert($concert));
        });
    }

    /**
     * Fungsi book_tickets di database mengunci stok dan menentukan harga.
     *
     * @throws SupabaseException pesan berisi kode seperti "not_enough_tickets"
     */
    public function book(string $accessToken, int $concertId, int $quantity, string $seatType, string $phone): TicketPurchase
    {
        $response = $this->send(fn () => $this->client->request()->withToken($accessToken)->post('/rest/v1/rpc/book_tickets', [
            'p_concert_id' => $concertId,
            'p_quantity' => $quantity,
            'p_seat_type' => $seatType,
            'p_phone' => $phone,
        ]));

        return $this->toPurchase($response->json(), null);
    }

    private function send(callable $call): Response
    {
        try {
            $response = $call();
        } catch (ConnectionException $exception) {
            throw new SupabaseException('unreachable', previous: $exception);
        }

        if (! $response->successful()) {
            throw new SupabaseException((string) ($response->json('message') ?? 'HTTP '.$response->status()), $response->status());
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function toConcert(array $row): Concert
    {
        return (new Concert)->forceFill($row);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function toPurchase(array $row, ?Concert $concert): TicketPurchase
    {
        $purchase = (new TicketPurchase)->forceFill($row);

        if ($concert) {
            $purchase->setRelation('concert', $concert);
        }

        return $purchase;
    }
}
