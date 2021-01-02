<?php


namespace Tests\Unit\Billing;


use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function getPaymentGateway(): PaymentGateway
    {
        return new FakePaymentGateway();
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
