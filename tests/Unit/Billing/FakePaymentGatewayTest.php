<?php


namespace Tests\Unit\Billing;


use App\Billing\FakePaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase {
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful(): void {
        $paymentGateway = new FakePaymentGateway();
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        self::assertEquals(2500, $paymentGateway->totalCharges());
    }
}
