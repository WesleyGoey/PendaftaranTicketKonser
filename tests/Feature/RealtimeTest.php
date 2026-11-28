<?php

namespace Tests\Feature;

use App\Realtime\ConcertChannel;
use App\Realtime\RealtimeSocket;
use Tests\Concerns\FakesSupabase;
use Tests\TestCase;

class RealtimeTest extends TestCase
{
    use FakesSupabase;

    private function change(int $id, int $available): array
    {
        return [
            'topic' => ConcertChannel::TOPIC,
            'event' => 'postgres_changes',
            'payload' => ['data' => ['type' => 'UPDATE', 'record' => ['id' => $id, 'available_tickets' => $available]]],
        ];
    }

    public function test_channel_subscribes_to_the_concerts_table_only(): void
    {
        $channel = app(ConcertChannel::class);

        $this->assertSame(
            [['event' => '*', 'schema' => 'public', 'table' => 'concerts']],
            $channel->joinMessage()['payload']['config']['postgres_changes'],
        );
        $this->assertSame(
            'wss://abc.supabase.co/realtime/v1/websocket?apikey=k&vsn=1.0.0',
            $channel->url('https://abc.supabase.co/', 'k'),
        );
    }

    public function test_changes_update_only_the_affected_concert(): void
    {
        $channel = app(ConcertChannel::class);

        $this->assertEquals([1 => 98, 2 => 50], $channel->apply([1 => 100, 2 => 50], $this->change(1, 98)));
        $this->assertEquals([1 => 100], $channel->apply([1 => 100, 2 => 50], [
            'payload' => ['data' => ['type' => 'DELETE', 'record' => [], 'old_record' => ['id' => 2]]],
        ]));
    }

    public function test_purchase_changes_signal_realtime(): void
    {
        $sql = file_get_contents(base_path('supabase/schema.sql'));

        $this->assertStringContainsString('create trigger ticket_purchases_realtime', $sql);
        $this->assertStringContainsString('after insert or update or delete on public.ticket_purchases', $sql);
    }

    public function test_channel_status_messages_are_recognised(): void
    {
        $channel = app(ConcertChannel::class);
        $message = fn (string $event, string $status) => ['topic' => ConcertChannel::TOPIC, 'event' => $event, 'payload' => ['status' => $status]];

        $this->assertSame('subscribed', $channel->handle($message('system', 'ok')));
        $this->assertSame('failed', $channel->handle($message('system', 'error')));
        $this->assertSame('failed', $channel->handle($message('phx_reply', 'error')));
        $this->assertSame('ignored', $channel->handle(['topic' => 'phoenix', 'event' => 'phx_reply']));
    }

    private function channelStatus(string $status): array
    {
        return ['topic' => ConcertChannel::TOPIC, 'event' => 'system', 'payload' => ['status' => $status]];
    }

    private function fakeSocket(array $messages): object
    {
        $socket = new class extends RealtimeSocket
        {
            public array $messages = [];

            public array $sent = [];

            public bool $closed = false;

            public function connect(string $url): void {}

            public function send(array $message): void
            {
                $this->sent[] = $message;
            }

            public function receive(): ?array
            {
                if ($this->messages) {
                    return array_shift($this->messages);
                }
                usleep(100_000);

                return null;
            }

            public function close(): void
            {
                $this->closed = true;
            }
        };
        $socket->messages = $messages;
        $this->app->instance(RealtimeSocket::class, $socket);
        config(['services.supabase.events_seconds' => 1]);

        return $socket;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function dataLines(string $stream): array
    {
        preg_match_all('/^data: (.+)$/m', $stream, $matches);

        return array_map(fn (string $line) => json_decode($line, true), $matches[1]);
    }

    public function test_events_subscribes_by_itself_and_forwards_changes(): void
    {
        $this->fakeSupabase();
        $socket = $this->fakeSocket([$this->channelStatus('ok'), $this->change(1, 97)]);

        $response = $this->get(route('events'));
        $events = $this->dataLines($response->streamedContent());

        $this->assertStringStartsWith('text/event-stream', $response->headers->get('Content-Type'));
        $this->assertEmpty($response->headers->getCookies());
        $this->assertSame('phx_join', $socket->sent[0]['event']);
        $this->assertCount(2, $events);
        $this->assertTrue($events[0]['snapshot']);
        $this->assertSame(97, $events[1]['concerts']['1']);
        $this->assertNotSame($events[0]['version'], $events[1]['version']);
        $this->assertTrue($socket->closed);
    }

    public function test_reconnecting_with_unchanged_data_keeps_the_same_version(): void
    {
        $this->fakeSupabase();

        $this->fakeSocket([$this->channelStatus('ok')]);
        $first = $this->dataLines($this->get(route('events'))->streamedContent());
        $this->fakeSocket([$this->channelStatus('ok')]);
        $second = $this->dataLines($this->get(route('events'))->streamedContent());

        $this->assertSame($first[0]['version'], $second[0]['version']);
    }

    public function test_a_rejected_channel_closes_the_stream_and_slows_reconnecting(): void
    {
        $socket = $this->fakeSocket([$this->channelStatus('error')]);

        $stream = $this->get(route('events'))->streamedContent();

        $this->assertStringNotContainsString('data:', $stream);
        $this->assertStringContainsString('retry: 10000', $stream);
        $this->assertTrue($socket->closed);
    }
}
