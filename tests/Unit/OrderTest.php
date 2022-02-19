<?php

namespace Tests\Unit;

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function converting_to_an_array(): void
    {
        $order = factory(Order::class)->create(
            [
                'confirmation_number' => 'ORDERCONFIRMATION1234',
                'email' => 'jane@example.com',
                'amount' => 6000,
            ]
        );
        $order->tickets()->saveMany(factory(Ticket::class)->times(5)->create());
        $result = $order->toArray();

        self::assertEquals(
            [
                'confirmation_number' => 'ORDERCONFIRMATION1234',
                'email' => 'jane@example.com',
                'ticket_quantity' => 5,
                'amount' => 6000
            ],
            $result
        );
    }

    /** @test */
    public function retrieving_an_order_by_confirmation_number(): void
    {
        /** @var Order $order */
        $order = factory(Order::class)->create(['confirmation_number' => 'ORDERCONFIRMATION1234']);

        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');

        self::assertEquals($order->id, $foundOrder->id);
    }

    /** @test */
    public function retrieving_a_nonexistent_order_by_confirmation_number_throws_an_exception(): void
    {
        $this->expectException(ModelNotFoundException::class);

        Order::findByConfirmationNumber('NOTANORDERCONFIRMATION1234');
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
