<?php

namespace Database\Factories;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConcertFactory extends Factory
{

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(),
            'title' => "{$this->faker->firstName}'s Band",
            'additional_information' => "For tickets, call {$this->faker->phoneNumber}.",
            'subtitle' => "with {$this->faker->firstName}'s Fake Openers",
            'date' => Carbon::parse('+2 weeks'),
            'venue' => "{$this->faker->firstName}'s Bar",
            'venue_address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'zip' => $this->faker->postcode,
            'ticket_price' => $this->faker->numberBetween(1500, 10000),
            'ticket_quantity' => $this->faker->numberBetween(20, 100),
        ];
    }

    public function published(): ConcertFactory
    {
        return $this->state(function () {
            return [
                'published_at' => Carbon::parse('-1 day'),
            ];
        });
    }

    public function unpublished(): ConcertFactory
    {
        return $this->state(function () {
            return [
                'published_at' => null,
            ];
        });
    }

    public static function createPublished($overrides = [])
    {
        $concert = Concert::factory()->create($overrides);

        $concert->publish();

        return $concert;
    }

    public static function createUnpublished($overrides = [])
    {
        return Concert::factory()->unpublished()->create($overrides);
    }
}
