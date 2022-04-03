<?php

use App\Order;
use App\Ticket;

class OrderFactory
{
    public static function createForConcert($concert, $overrides = [], $ticketQuantity = 1): Order
    {
        /** @var Order $order */
        $order = factory(Order::class)->create($overrides);
        /** @var array{Ticket} $tickets */
        $tickets = factory(Ticket::class, $ticketQuantity)
            ->create(
                [
                    'concert_id' => $concert->id,
                ]
            );
        $order->tickets()->saveMany($tickets);

        return $order;
    }
}
