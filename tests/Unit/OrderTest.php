<?php

namespace Tests\Unit;

use App\Billing\Charge;
use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function converting_to_an_array(): void
    {
        $order = Order::factory()->create(
            [
                'confirmation_number' => 'ORDERCONFIRMATION1234',
                'email' => 'jane@example.com',
                'amount' => 6000,
            ]
        );
        $order->tickets()->saveMany(
            [
                Ticket::factory()->create(['code' => 'TICKETCODE1']),
                Ticket::factory()->create(['code' => 'TICKETCODE2']),
                Ticket::factory()->create(['code' => 'TICKETCODE3']),
            ]
        );
        $result = $order->toArray();

        self::assertEquals(
            [
                'confirmation_number' => 'ORDERCONFIRMATION1234',
                'email' => 'jane@example.com',
                'amount' => 6000,
                'tickets' => [
                    ['code' => 'TICKETCODE1'],
                    ['code' => 'TICKETCODE2'],
                    ['code' => 'TICKETCODE3'],
                ],
            ],
            $result
        );
    }

    /** @test */
    public function retrieving_an_order_by_confirmation_number(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create(['confirmation_number' => 'ORDERCONFIRMATION1234']);

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
    public function creating_an_order_from_email_tickets_and_charge(): void
    {
//        $tickets = factory(Ticket::class, 3)->create();
        $charge = new Charge(['amount' => 3600, 'card_last_four' => '1234']);

        $tickets = collect(
            [
                Mockery::spy(Ticket::class),
                Mockery::spy(Ticket::class),
                Mockery::spy(Ticket::class),
            ]
        );

        $order = Order::forTickets('john@example.com', $tickets, $charge);

        self::assertEquals('john@example.com', $order->email);
        self::assertEquals(3600, $order->amount);
        self::assertEquals('1234', $order->card_last_four);
        $tickets->each->shouldHaveReceived('claimFor', [$order]);
    }
}
