<?php

/** @var Factory $factory */

use App\Concert;
use App\Order;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

$factory->define(
    Order::class,
    static function (Faker $faker) {
        return [
            'amount' => $faker->numberBetween(1500, 10000),
            'email' => $faker->email,
            'card_last_four' => '4242',
            'confirmation_number' => 'ORDERCONFIRMATION1234',
        ];
    }
);

$factory->state(Concert::class, 'published', ['published_at' => Carbon::parse('-1 day')]);

$factory->state(Concert::class, 'unpublished', ['published_at' => null]);
