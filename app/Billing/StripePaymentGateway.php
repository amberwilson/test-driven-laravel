<?php


namespace App\Billing;


use Carbon\Carbon;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\InvalidRequestException;
use Stripe\Token;

class StripePaymentGateway implements PaymentGateway
{
    public const TEST_CARD_NUMBER = '4242424242424242';

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge(int $amount, string $token, string $destinationAccountId): Charge
    {
        try {
            $stripeCharge = \Stripe\Charge::create(
                [
                    'amount' => $amount,
                    'currency' => 'cad',
                    'source' => $token,
                    'destination' => [
                        'account' => $destinationAccountId,
                        'amount' => $amount * 0.9,
                    ]
                ],
                $this->apiKey
            );

            return new Charge(
                [
                    'amount' => $stripeCharge['amount'],
                    'card_last_four' => $stripeCharge['source']['last4'],
                    'destination' => $destinationAccountId
                ]
            );
        } catch (InvalidRequestException $exception) {
            Log::info('InvalidRequestException when trying to charge', ['exception' => $exception]);
            throw new PaymentFailedException('Invalid payment token provided - ckjee3lg600009tvqbjhx8xx0');
        }
    }

    public function getValidTestToken(string $cardNumber = self::TEST_CARD_NUMBER): string
    {
        return Token::create(
            [
                'card' => [
                    'number' => $cardNumber,
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

        return $this->chargesSince($lastCharge)
            ->map(static function ($stripeCharge) {
                return new Charge(
                    [
                        'amount' => $stripeCharge['amount'],
                        'card_last_four' => $stripeCharge['source']['last4']
                    ]
                );
            });
    }

    private function lastCharge(): ?\Stripe\Charge
    {
        return Arr::first(
            \Stripe\Charge::all(
                ['limit' => 1],
                ['api_key' => $this->apiKey]
            )['data']
        );
    }

    private function chargesSince(?\Stripe\Charge $charge): Collection
    {
        $newCharges = \Stripe\Charge::all(
            [
                'ending_before' => $charge
            ],
            ['api_key' => $this->apiKey]
        )['data'];

        return collect($newCharges);
    }
}
