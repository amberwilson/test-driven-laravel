<?php

/** @var Factory $factory */

use App\Concert;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Ticket::class,
    static function () {
        return [
            'concert_id' => factory(Concert::class)
        ];
    }
);

$factory->state(
    Ticket::class,
    'reserved',
    function () {
        return [
            'reserved_at' => Carbon::now(),
        ];
    }
);
