<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase {

    use DatabaseMigrations;

    /** @test */
    public function customer_can_purchase_concerts_tickets(): void {
        // Arrange
        // Create a concert
        $concert = factory(Concert::class)->create(['price' => 3250]);

        // Act
        // Purchase concert tickets
        $response = $this->json(
            'POST',
            "/concerts/{$concert->id}/orders",
            ['email'           => 'jane@example.com',
             'ticket_quantity' => 3,
             'payment_token'   => $paymentGateway->getValidTestToken(),]
        );

        // Assert
        // Make sure the customer was charged the correct amount
        self::assertEquals(9750, $paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'jane@example.com')->first();
        self::assertNotNull($order);
        self::assertEquals(3, $order->tickets->count());
    }
}
