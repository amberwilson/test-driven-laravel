<?php

namespace App\Facades;

use App\OrderConfirmationNumberGenerator;
use Illuminate\Support\Facades\Facade;

class OrderConfirmationNumber extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return OrderConfirmationNumberGenerator::class;
    }
}
