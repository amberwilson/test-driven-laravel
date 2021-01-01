<?php


namespace Tests\Unit\Billing;


use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Billing\StripePaymentGateway;
use Tests\TestCase;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPaymentGateway(): PaymentGateway
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }

    /** @test */
    public function charges_with_a_valid_payment_token_are_successful(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(
            function (PaymentGateway $paymentGateway) {
                $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            }
        );

        self::assertCount(1, $newCharges);
        self::assertSame(2500, $newCharges->sum());
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail(): void
    {
        try {
            $paymentGateway = $this->getPaymentGateway();

            $paymentGateway->newChargesDuring(
                function (PaymentGateway $paymentGateway) {
                    $paymentGateway->charge(2500, 'invalid-payment-token');
                }
            );
        } catch (PaymentFailedException $e) {
            self::assertEquals('Invalid payment token provided - ckjee3lg600009tvqbjhx8xx0', $e->getMessage());
//            self::assertCount(0, $this->newCharges()); // FIXME

            return;
        }

        self::fail('Charging with an invalid payment token did not throw a PaymentFailedException.');
    }
}
