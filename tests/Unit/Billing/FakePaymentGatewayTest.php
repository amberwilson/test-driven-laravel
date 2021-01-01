<?php


namespace Tests\Unit\Billing;


use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    protected function getPaymentGateway(): PaymentGateway
    {
        return new FakePaymentGateway();
    }

    /** @test */
    public function can_fetch_charges_created_during_a_callback(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(
            function (PaymentGateway $paymentGateway) {
                $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
                $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
            }
        );

        self::assertCount(2, $newCharges);
        self::assertSame([4000, 5000], $newCharges->all());
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
            self::assertEquals('Invalid payment token provided - ckdxcu4bf00006pvq5rxibgj8', $e->getMessage());

            return;
        }

        self::fail('Charge succeeded even thought the payment token was invalid.');
    }

    /** @test */
    public function running_a_hook_before_the_first_charge(): void
    {
        $paymentGateway = $this->getPaymentGateway();
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
