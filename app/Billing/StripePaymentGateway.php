<?php


namespace App\Billing;


use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;

class StripePaymentGateway implements PaymentGateway
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge(int $amount, string $token): void
    {
        try {
            Charge::create(
                [
                    'amount' => $amount,
                    'currency' => 'cad',
                    'source' => $token,
                ],
                $this->apiKey
            );
        } catch (InvalidRequestException $exception) {
            throw new PaymentFailedException('Invalid payment token provided - ckjee3lg600009tvqbjhx8xx0');
        }
    }
}
