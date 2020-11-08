<?php

namespace Tests\Unit;

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function converting_to_an_array(): void
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 1200])->addTickets(5);
        $order = $concert->orderTickets('jane@example.com', 5);

        $result = $order->toArray();

        self::assertEquals(['email' => 'jane@example.com', 'ticket_quantity' => 5, 'amount' => 6000], $result);
    }

    /** @test */
    public function creating_an_order_from_email_tickets_and_amount(): void
    {
        $concert = factory(Concert::class)->create()->addTickets(5);

        self::assertEquals(5, $concert->ticketsRemaining());

        $order = Order::forTickets('john@example.com', $concert->findTickets(3), 3600);

        self::assertEquals('john@example.com', $order->email);
        self::assertEquals(3, $order->ticketQuantity());
        self::assertEquals(3600, $order->amount);
        self::assertEquals(2, $concert->ticketsRemaining());
    }
}
