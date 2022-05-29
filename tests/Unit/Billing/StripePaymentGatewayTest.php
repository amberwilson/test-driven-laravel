<?php


namespace Tests\Unit\Billing;


use App\Billing\PaymentGateway;
use App\Billing\StripePaymentGateway;
use Illuminate\Support\Arr;
use Stripe\Charge;
use Stripe\Transfer;
use Tests\TestCase;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function getPaymentGateway(): PaymentGateway
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }

    /** @test */
    public function ninety_percent_of_the_payment_is_transferred_to_the_destination_account(): void
    {
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        $paymentGateway->charge(
            5000,
            $paymentGateway->getValidTestToken(),
            env('STRIPE_TEST_PROMOTER_ID')
        );

        $lastStripeCharge = Arr::first(
            Charge::all(
                ['limit' => 1],
                ['api_key' => config('services.stripe.secret')]
            )['data']
        );

        self::assertEquals(5000, $lastStripeCharge['amount']);
        self::assertEquals(env('STRIPE_TEST_PROMOTER_ID'), $lastStripeCharge['destination']);


        $transfer = Transfer::retrieve($lastStripeCharge['transfer'], ['api_key' => config('services.stripe.secret')]);
        
        self::assertEquals(4500, $transfer['amount']);
//        self::assertEquals(500, $transfer['fee']);
    }
}
