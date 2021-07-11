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
                'confirmation_number' => 'ORDERCONFIRMATION1234'
            ]
        );
        factory(Ticket::class)->create(
            [
                'concert_id' => $concert->id,
                'order_id' => $order->id,
            ]
        );

        // Visit the order confirmation page
        $response = $this->get("/orders/{$order->confirmation_number}");

        $response->assertStatus(200);

        // Assert we see the correct order details
        $response->assertViewHas('order', $order);
    }
}
