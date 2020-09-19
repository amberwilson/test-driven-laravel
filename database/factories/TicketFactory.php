<?php

/** @var Factory $factory */

use App\Concert;
use App\Ticket;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Ticket::class,
    static function (Faker $faker) {
        return [
            'concert_id' => factory(Concert::class)
        ];
    }
);
