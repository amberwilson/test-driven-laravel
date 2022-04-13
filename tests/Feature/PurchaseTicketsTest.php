<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Facades\OrderConfirmationNumber;
use App\Facades\TicketCode;
use App\Mail\OrderConfirmationEmail;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{

    use RefreshDatabase;

    private FakePaymentGateway $paymentGateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);

        Mail::fake();
    }

    /** @test */
    public function customer_can_purchase_tickets_to_a_published_concert(): void
    {
        // Arrange
        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATION1234');
        TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');

        // Create a concert
        $concert = ConcertFactory::createPublished(['ticket_price' => 3250, 'ticket_quantity' => 3]);

        // Act
        // Purchase concert tickets
        $response = $this->orderTickets(
            $concert,
            [
                'email' => 'jane@example.com',
                'ticket_quantity' => 3,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]
        );

        // Assert
        $response->assertStatus(201);

        $response->assertJson(
            [
                'confirmation_number' => 'ORDERCONFIRMATION1234',
                'email' => 'jane@example.com',
                'amount' => 9750,
                'tickets' => [
                    ['code' => 'TICKETCODE1'],
                    ['code' => 'TICKETCODE2'],
                    ['code' => 'TICKETCODE3'],
                ],
            ]
        );

        $order = $concert->ordersFor('jane@example.com')->first();

        // Make sure the customer was charged the correct amount
        self::assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        self::assertTrue($concert->hasOrderFor('jane@example.com'));
        self::assertEquals(3, $order->ticketQuantity());

        // Make sure the user gets a confirmation email
        Mail::assertSent(OrderConfirmationEmail::class, static function (OrderConfirmationEmail $mail) use ($order) {
            return $mail->hasTo('jane@example.com')
                && $mail->order->id === $order->id;
        });
    }

    private function orderTickets(Concert $concert, array $params): TestResponse
    {
        // Backup original request because secondary request will overwrite it - needed for nested requests
        $savedRequest = $this->app['request'];

        $response = $this->json(
            'POST',
            "/concerts/{$concert->id}/orders",
            $params
        );

        // Put back original request now that the secondary request is done
        $this->app['request'] = $savedRequest;

        return $response;
    }

    /** @test */
    public function an_order_is_not_created_if_payment_fails(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()
            ->published()
            ->create()
            ->addTickets(3);

        $response = $this->orderTickets(
            $concert,
            [
                'email' => 'jane@example.com',
                'ticket_quantity' => 3,
                'payment_token' => 'invalid-payment-token',
            ]
        );

        $response->assertStatus(422);
        self::assertFalse($concert->hasOrderFor('jane@example.com'));
        self::assertSame(3, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_purchase_to_an_unpublished_concert(): void
    {
        $concert = Concert::factory()
            ->unpublished()
            ->create()
            ->addTickets(3);

        $response = $this->orderTickets(
            $concert,
            [
                'email' => 'jane@example.com',
                'ticket_quantity' => 3,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $response->assertStatus(404);

        self::assertFalse($concert->hasOrderFor('jane@example.com'));
        self::assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test */
    public function cannot_purchase_more_tickets_than_remain(): void
    {
        $concert = Concert::factory()
            ->published()
            ->create()
            ->addTickets(50);

        $response = $this->orderTickets(
            $concert,
            [
                'email' => 'jane@example.com',
                'ticket_quantity' => 51,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $response->assertStatus(422);

        self::assertFalse($concert->hasOrderFor('jane@example.com'));
        self::assertEquals(0, $this->paymentGateway->totalCharges());
        self::assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase(): void
    {
        $concert = Concert::factory()
            ->published()
            ->create(
                [
                    'ticket_price' => 1200
                ]
            )
            ->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(
            function ($paymentGateway) use ($concert) {
                $response = $this->orderTickets(
                    $concert,
                    [
                        'email' => 'personB@example.com',
                        'ticket_quantity' => 1,
                        'payment_token' => $paymentGateway->getValidTestToken(),
                    ]
                );

                $response->assertStatus(422);

                self::assertFalse($concert->hasOrderFor('personB@example.com'));
                self::assertEquals(0, $paymentGateway->totalCharges());
            }
        );

        $response = $this->orderTickets(
            $concert,
            [
                'email' => 'personA@example.com',
                'ticket_quantity' => 3,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]
        );


        $response->assertStatus(201);

        // Make sure the customer was charged the correct amount
        self::assertEquals(3600, $this->paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        self::assertTrue($concert->hasOrderFor('personA@example.com'));
        self::assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
    }

    // region Validation

    private function assertValidationError(string $field, TestResponse $response)
    {
        $response->assertStatus(422);
        self::assertArrayHasKey($field, $response->decodeResponseJson()['errors']);
    }

    /** @test */
    public function email_is_required_to_purchase_tickets(): void
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets(
            $concert,
            [
                'ticket_quantity' => 3,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertValidationError('email', $response);
    }

    /** @test */
    public function email_must_be_valid_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets(
            $concert,
            [
                'email' => 'not-a-valid-email-address',
                'ticket_quantity' => 3,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertValidationError('email', $response);
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets(
            $concert,
            [
                'email' => 'jane@example.com',
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertValidationError('ticket_quantity', $response);
    }

    /** @test */
    public function ticket_quantity_be_at_least_1_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets(
            $concert,
            [
                'email' => 'jane@example.com',
                'ticket_quantity' => 0,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]
        );

        $this->assertValidationError('ticket_quantity', $response);
    }

    /** @test */
    public function payment_token_is_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets(
            $concert,
            [
                'email' => 'jane@example.com',
                'ticket_quantity' => 1,
            ]
        );

        $this->assertValidationError('payment_token', $response);
    }
    // endregion Validation
}
