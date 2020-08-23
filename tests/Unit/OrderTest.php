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
    public function tickets_are_released_when_an_order_is_cancelled(): void
    {
        $concert = factory(Concert::class)->create()->addTickets(10);
        $order = $concert->orderTickets('jane@example.com', 5);
        self::assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        self::assertEquals(10, $concert->ticketsRemaining());

        self::assertNull(Order::find($order->id));
    }
}
