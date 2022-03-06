<?php

/** @var Factory $factory */

use App\Concert;
use App\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Concert::class,
    static function (Faker $faker) {
        return [
            'user_id' => factory(User::class),
            'title' => "{$faker->firstName}'s Band",
            'subtitle' => "with {$faker->firstName}'s Fake Openers",
            'date' => Carbon::parse('+2 weeks'),
            'ticket_price' => $faker->numberBetween(1500, 10000),
            'venue' => "{$faker->firstName}'s Bar",
            'venue_address' => $faker->streetAddress,
            'city' => $faker->city,
            'state' => $faker->state,
            'zip' => $faker->postcode,
            'additional_information' => "For tickets, call {$faker->phoneNumber}."
        ];
    }
);

$factory->state(Concert::class, 'published', ['published_at' => Carbon::parse('-1 day')]);

$factory->state(Concert::class, 'unpublished', ['published_at' => null]);
