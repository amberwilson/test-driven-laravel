<?php

namespace App\Facades;

use App\TicketCodeGenerator;
use Illuminate\Support\Facades\Facade;

class TicketCode extends Facade
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
        return TicketCodeGenerator::class;
    }
}
