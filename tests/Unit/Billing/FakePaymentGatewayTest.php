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
                $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');
                $timesCallbackRan++;
                self::assertEquals(2500, $paymentGateway->totalCharges());
            }
        );

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');

        self::assertEquals(1, $timesCallbackRan);
        self::assertEquals(5000, $paymentGateway->totalCharges());
    }

    /** @test */
    public function can_get_total_charges_for_a_specific_account(): void
    {
        $this->withoutExceptionHandling();
        $paymentGateway = new FakePaymentGateway();

        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken(), 'test_acct_0000');
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        $paymentGateway->charge(4000, $paymentGateway->getValidTestToken(), 'test_acct_1234');

        self::assertEquals(6500, $paymentGateway->totalChargesFor('test_acct_1234'));
    }
}
