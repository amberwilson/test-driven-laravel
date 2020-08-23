<?php

namespace Tests\Unit;

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketTest extends TestCase {
    use DatabaseMigrations;

    /** @test */
    public function a_ticket_can_be_released(): void {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $order->tickets()->first();
        self::assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        self::assertNull($ticket->fresh()->order_id);
    }
}