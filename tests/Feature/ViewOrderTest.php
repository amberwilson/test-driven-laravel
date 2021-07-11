<?php

namespace Tests\Feature;

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_their_order_confirmation(): void
    {
        $this->withoutExceptionHandling();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->create();
        /** @var Order $order */
        $order = factory(Order::class)->create(
            [
                'confirmation_number' => 'ORDERCONFIRMATION1234',
                'card_last_four' => '1881',
                'amount' => 8500,
            ]
        );
        $ticket1 = factory(Ticket::class)->create(
            [
                'concert_id' => $concert->id,
                'order_id' => $order->id,
                'code' => 'TICKETCODE123',
            ]
        );
        $ticket2 = factory(Ticket::class)->create(
            [
                'concert_id' => $concert->id,
                'order_id' => $order->id,
                'code' => 'TICKETCODE456',
            ]
        );

        // Visit the order confirmation page
        $response = $this->get("/orders/{$order->confirmation_number}");

        $response->assertStatus(200);

        // Assert we see the correct order details
        $response->assertViewHas('order', $order);

        $response->assertSee('ORDERCONFIRMATION1234');
        $response->assertSee('$85.00');
        $response->assertSee('**** **** **** 1881');
        $response->assertSee('TICKETCODE123');
        $response->assertSee('TICKETCODE456');
    }

}
