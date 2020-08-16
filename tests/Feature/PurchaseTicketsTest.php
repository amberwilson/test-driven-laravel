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

    private function orderTickets(Concert $concert, array $params): \Illuminate\Testing\TestResponse {
        return $this->json(
            'POST',
            "/concerts/{$concert->id}/orders",
            $params
        );
    }

    private function assertValidationError(string $field, \Illuminate\Testing\TestResponse $response) {
        $response->assertStatus(422);
        self::assertArrayHasKey($field, $response->decodeResponseJson()['errors']);
    }

    /** @test */
    public function customer_can_purchase_concerts_tickets(): void {
        // Arrange
        // Create a concert
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 3250]);

        // Act
        // Purchase concert tickets
        $response = $this->orderTickets(
            $concert,
            [
                'email'           => 'jane@example.com',
                'ticket_quantity' => 3,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]
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

        $response = $this->orderTickets(
            $concert,
            [
                'ticket_quantity' => 3,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertValidationError('email', $response);
    }

    /** @test */
    public function email_must_be_valid_to_purchase_tickets() {
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets(
            $concert,
            [
                'email'           => 'not-a-valid-email-address',
                'ticket_quantity' => 3,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertValidationError('email', $response);
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_tickets() {
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets(
            $concert,
            [
                'email'         => 'jane@example.com',
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertValidationError('ticket_quantity', $response);
    }

    /** @test */
    public function ticket_quantity_be_at_least_1_to_purchase_tickets() {
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets(
            $concert,
            [
                'email'           => 'jane@example.com',
                'ticket_quantity' => 0,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertValidationError('ticket_quantity', $response);
    }

    /** @test */
    public function payment_token_is_required_to_purchase_tickets() {
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets(
            $concert,
            [
                'email'           => 'jane@example.com',
                'ticket_quantity' => 1,
            ]
        );

        $this->assertValidationError('payment_token', $response);
    }
}
