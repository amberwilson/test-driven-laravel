<?php


namespace Tests\Unit\Billing;


use App\Billing\StripePaymentGateway;
use Illuminate\Support\Arr;
use Stripe\Charge;
use Tests\TestCase;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    private ?Charge $lastCharge;

    public function setUp(): void
    {
        parent::setUp();

        $this->lastCharge = $this->lastCharge();
    }

    /** @test */
    public function charges_with_a_valid_payment_token_are_successful(): void
    {
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        $paymentGateway->charge(2500, 'tok_amex');

        self::assertCount(1, $this->newCharges());
        self::assertSame(2500, $this->lastCharge()->amount);
    }

    private function lastCharge(): ?Charge
    {
        return Arr::first(
            Charge::all(
                ['limit' => 1],
                ['api_key' => config('services.stripe.secret')]
            )['data']
        );
    }

    private function newCharges(): array
    {
        return Charge::all(
            [
                'ending_before' => $this->lastCharge
            ],
            ['api_key' => config('services.stripe.secret')]
        )['data'];
    }
}
