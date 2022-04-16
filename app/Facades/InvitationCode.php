<?php

namespace App\Facades;

use App\InvitationCodeGenerator;
use Illuminate\Support\Facades\Facade;
use RuntimeException;

class InvitationCode extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return InvitationCodeGenerator::class;
    }
}
