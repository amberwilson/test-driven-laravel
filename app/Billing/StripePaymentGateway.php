<?php


namespace App\Billing;


use Stripe\Charge;

class StripePaymentGateway implements PaymentGateway
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge(int $amount, string $token): void
    {
        Charge::create(
            [
                'amount' => $amount,
                'currency' => 'cad',
                'source' => $token,
            ],
            $this->apiKey
        );
    }
}
