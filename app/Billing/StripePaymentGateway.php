<?php


namespace App\Billing;


use Carbon\Carbon;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;
use Stripe\Token;

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

    public function getValidTestToken(): string
    {
        return Token::create(
            [
                'card' => [
                    'number' => '4242424242424242',
                    'exp_month' => 1,
                    'exp_year' => Carbon::now()->addYear()->year,
                    'cvc' => '123',
                ]
            ],
            ['api_key' => $this->apiKey]
        )->id;
    }

    public function newChargesDuring(Closure $callback): Collection
    {
        $lastCharge = $this->lastCharge();

        $callback($this);

        return $this->chargesSince($lastCharge)->pluck('amount');
    }

    private function lastCharge(): ?Charge
    {
        return Arr::first(
            Charge::all(
                ['limit' => 1],
                ['api_key' => $this->apiKey]
            )['data']
        );
    }

    private function chargesSince(?Charge $charge): Collection
    {
        $newCharges = Charge::all(
            [
                'ending_before' => $charge
            ],
            ['api_key' => $this->apiKey]
        )['data'];

        return collect($newCharges);
    }
}
