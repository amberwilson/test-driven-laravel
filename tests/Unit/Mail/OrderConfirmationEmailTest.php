<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderConfirmationEmail;
use App\Order;
use Tests\TestCase;

class OrderConfirmationEmailTest extends TestCase
{
    /** @test */
    public function email_contains_a_link_to_the_order_confirmation_page(): void
    {
        $order = factory(Order::class)->make(
            [
                'confirmation_number' => 'ORDERCONFIRMATION1234',
            ]
        );

        $orderConfirmationEmail = new OrderConfirmationEmail($order);
        $rendered = $orderConfirmationEmail->render();

        self::assertStringContainsString(url('/orders/ORDERCONFIRMATION1234'), $rendered);
    }

    /** @test */
    public function email_has_a_subject(): void
    {
        $order = factory(Order::class)->make();

        $orderConfirmationEmail = new OrderConfirmationEmail($order);

        self::assertEquals('Your TicketBeast Order', $orderConfirmationEmail->build()->subject);
    }
}
