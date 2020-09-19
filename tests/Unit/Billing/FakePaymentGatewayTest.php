<?php


namespace Tests\Unit\Billing;


use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful(): void
    {
        $paymentGateway = new FakePaymentGateway();
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        self::assertEquals(2500, $paymentGateway->totalCharges());
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail(): void
    {
        try {
            $paymentGateway = new FakePaymentGateway();
            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            self::assertEquals('Invalid payment token provided - ckdxcu4bf00006pvq5rxibgj8', $e->getMessage());

            return;
        }

        self::fail('Charge succeeded even thought the payment token was invalid.');
    }

    /** @test */
    public function running_a_hook_before_the_first_charge(): void
    {
        $paymentGateway = new FakePaymentGateway();
        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(
            static function (FakePaymentGateway $paymentGateway) use (&$timesCallbackRan) {
                $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
                $timesCallbackRan++;
                self::assertEquals(2500, $paymentGateway->totalCharges());
            }
        );

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        self::assertEquals(1, $timesCallbackRan);
        self::assertEquals(5000, $paymentGateway->totalCharges());
    }
}
