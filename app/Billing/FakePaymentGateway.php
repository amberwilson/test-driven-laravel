<?php


namespace App\Billing;


use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{

    public const TEST_CARD_NUMBER = '4242424242424242';

    private Collection $charges;
    private Collection $tokens;
    private Closure|null $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    public function totalCharges(): int
    {
        return $this->charges->map->amount()->sum();
    }

    public function totalChargesFor(string $accountId): int
    {
        return $this->charges
            ->filter(fn(Charge $charge) => $charge->destination() === $accountId)
            ->map
            ->amount()
            ->sum();
    }

    public function charge(int $amount, string $token, string $destinationAccountId): Charge
    {
        if (isset($this->beforeFirstChargeCallback)) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if (!$this->tokens->has($token)) {
            throw new PaymentFailedException('Invalid payment token provided - ckdxcu4bf00006pvq5rxibgj8');
        }

        return $this->charges[] = new Charge(
            [
                'amount' => $amount,
                'card_last_four' => substr($this->tokens[$token], -4),
                'destination' => $destinationAccountId,
            ]
        );
    }

    public function getValidTestToken(string $cardNumber = self::TEST_CARD_NUMBER): string
    {
        $token = 'fake_token' . Str::random(24);
        $this->tokens[$token] = $cardNumber;

        return $token;
    }

    public function beforeFirstCharge(Closure $callback): void
    {
        $this->beforeFirstChargeCallback = $callback;
    }

    public function newChargesDuring(Closure $callback): Collection
    {
        $chargesFrom = $this->charges->count();

        $callback($this);

        return $this->charges->slice($chargesFrom)->reverse()->values();
    }
}
