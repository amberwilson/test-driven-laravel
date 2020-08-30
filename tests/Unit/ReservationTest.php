<?php


namespace Tests\Unit;

use App\Concert;
use App\Reservation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;


class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function calculating_the_total_cost(): void
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 1200])->addTickets(3);

        $tickets = $concert->findTickets(3);

        $reservation = new Reservation($tickets);

        self::assertEquals(3600, $reservation->totalCost());
    }
}
