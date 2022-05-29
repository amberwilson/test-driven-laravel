<?php

namespace App\Billing;

use Closure;
use Illuminate\Support\Collection;

interface PaymentGateway
{
    public function charge(int $amount, string $token, string $destinationAccountId);

    public function getValidTestToken(): string;

    public function newChargesDuring(Closure $callback): Collection;
}
