<?php


namespace Tests\Unit;

use App\Reservation;
use Tests\TestCase;


class ReservationTest extends TestCase
{
    /** @test */
    public function calculating_the_total_cost(): void
    {
        $tickets = collect(
            [
                (object)['price' => 1200],
                (object)['price' => 1200],
                (object)['price' => 1200],
            ]
        );

        $reservation = new Reservation($tickets);

        self::assertEquals(3600, $reservation->totalCost());
    }
}
