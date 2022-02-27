<?php

namespace Tests\Unit;

use App\Facades\TicketCode;
use App\Order;
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
        /** @var Ticket $ticket */
        $ticket = factory(Ticket::class)->states('reserved')->create();
        self::assertNotNull($ticket->reserved_at);

        $ticket->release();

        self::assertNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function a_ticket_can_be_claimed_for_an_order(): void
    {
        /** @var Order $order */
        $order = factory(Order::class)->create();
        /** @var Ticket $ticket */
        $ticket = factory(Ticket::class)->create(['code' => null]);
        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');

        $ticket->claimFor($order);

        self::assertContains($ticket->id, $order->tickets->pluck('id'));
        // assert ticket had expected ticket code generated
        self::assertEquals('TICKETCODE1', $ticket->code);

    }
}
