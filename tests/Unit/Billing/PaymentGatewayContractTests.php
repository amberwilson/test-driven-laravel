<?php


namespace Tests\Unit\Billing;


use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;

trait PaymentGatewayContractTests
{

    abstract public function getPaymentGateway(): PaymentGateway;

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
        self::assertSame([5000, 4000], $newCharges->all());
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail(): void
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(
            function (PaymentGateway $paymentGateway) {
                try {
                    $paymentGateway->charge(2500, 'invalid-payment-token');
                } catch (PaymentFailedException $e) {
                    return;
                }

                self::fail('Charge succeeded even thought the payment token was invalid.');
            }
        );

        self::assertCount(0, $newCharges);
    }
}
