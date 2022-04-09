<?php

namespace Database\Factories;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'concert_id' => Concert::factory(),
            'code' => Str::random(),
        ];
    }


    public function reserved(): TicketFactory
    {
        return $this->state(function () {
            return [
                'reserved_at' => Carbon::now(),
            ];
        });
    }
}
