<?php

namespace Database\Factories;

use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(1500, 10000),
            'email' => $this->faker->email,
            'card_last_four' => '4242',
            'confirmation_number' => 'ORDERCONFIRMATION1234',
        ];
    }

    public static function createForConcert($concert, $overrides = [], $ticketQuantity = 1): Order
    {
        /** @var Order $order */
        $order = Order::factory()->create($overrides);
        /** @var array{Ticket} $tickets */
        $tickets = Ticket::factory($ticketQuantity)
            ->create(
                [
                    'concert_id' => $concert->id,
                ]
            );
        $order->tickets()->saveMany($tickets);

        return $order;
    }
}
