<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase {

    use DatabaseMigrations;

    private $paymentGateway;

    public function setUp(): void {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    public function customer_can_purchase_concerts_tickets(): void {
        // Arrange
        // Create a concert
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 3250]);

        // Act
        // Purchase concert tickets
        $response = $this->json(
            'POST',
            "/concerts/{$concert->id}/orders",
            ['email'           => 'jane@example.com',
             'ticket_quantity' => 3,
             'payment_token'   => $this->paymentGateway->getValidTestToken(),]
        );

        // Assert
        $response->assertStatus(201);

        // Make sure the customer was charged the correct amount
        self::assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'jane@example.com')->first();
        self::assertNotNull($order);
        self::assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    public function email_is_required_to_purchase_tickets(): void {
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->json(
            'POST',
            "/concerts/{$concert->id}/orders",
            ['ticket_quantity' => 3,
             'payment_token'   => $this->paymentGateway->getValidTestToken(),]
        );

        $response->assertStatus(422);
        self::assertArrayHasKey('email', $response->decodeResponseJson()['errors']);
    }
}
