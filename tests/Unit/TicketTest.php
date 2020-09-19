<?php

namespace Tests\Unit;

use App\Concert;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_ticket_can_be_reserved(): void
    {
        /** @var Ticket $ticket */
        $ticket = factory(Ticket::class)->create();
        self::assertNull($ticket->reserved_at);

        $ticket->reserve();

        self::assertNotNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function a_ticket_can_be_released(): void
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $order->tickets()->first();
        self::assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        self::assertNull($ticket->fresh()->order_id);
    }
}
