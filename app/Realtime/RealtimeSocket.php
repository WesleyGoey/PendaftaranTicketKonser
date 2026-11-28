<?php

namespace App\Realtime;

use WebSocket\Client;
use WebSocket\Exception\ConnectionTimeoutException;
use WebSocket\Message\Text;
use WebSocket\Middleware\CloseHandler;
use WebSocket\Middleware\PingResponder;

/**
 * Pembungkus tipis koneksi WebSocket ke Supabase, supaya bisa diganti tiruan di test.
 */
class RealtimeSocket
{
    // Berlaku untuk menyambung dan membaca (pustakanya tidak memisahkan keduanya).
    private const TIMEOUT_SECONDS = 3;

    private ?Client $client = null;

    public function connect(string $url): void
    {
        $this->client = new Client($url);
        $this->client->setTimeout(self::TIMEOUT_SECONDS);
        $this->client->addMiddleware(new CloseHandler)->addMiddleware(new PingResponder);
    }

    /**
     * @param  array<string, mixed>  $message
     */
    public function send(array $message): void
    {
        $this->client->send(new Text(json_encode($message)));
    }

    /**
     * @return array<string, mixed>|null null berarti belum ada pesan
     */
    public function receive(): ?array
    {
        try {
            $message = $this->client->receive();
        } catch (ConnectionTimeoutException) {
            return null;
        }

        return $message instanceof Text ? json_decode($message->getContent(), true) : null;
    }

    public function close(): void
    {
        $this->client?->disconnect();
        $this->client = null;
    }
}
