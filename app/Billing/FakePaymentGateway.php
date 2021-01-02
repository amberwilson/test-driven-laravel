<?php


namespace App\Billing;


use Closure;
use Illuminate\Support\Collection;

class FakePaymentGateway implements PaymentGateway
{

    private $charges;
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }

    public function charge(int $amount, string $token): void
    {
        if ($this->beforeFirstChargeCallback !== null) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if ($token !== $this->getValidTestToken()) {
            throw new PaymentFailedException('Invalid payment token provided - ckdxcu4bf00006pvq5rxibgj8');
        }

        $this->charges[] = $amount;
    }

    public function getValidTestToken(): string
    {
        return 'valid-token';
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
