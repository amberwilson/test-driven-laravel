<?php


namespace App\Billing;


class FakePaymentGateway implements PaymentGateway {

    private $charges;

    public function __construct() {
        $this->charges = collect();
    }

    public function getValidTestToken(): string {
        return 'valid-token';
    }

    public function totalCharges() {
        return $this->charges->sum();
    }

    public function charge(int $amount, string $token) {
        if ($token !== $this->getValidTestToken()) {
            throw new PaymentFailedException('Invalid payment token provided - ckdxcu4bf00006pvq5rxibgj8');
        }

        $this->charges[] = $amount;
    }
}
