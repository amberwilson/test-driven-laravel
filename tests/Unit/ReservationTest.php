<?php


namespace Tests\Unit;

use App\Reservation;
use App\Ticket;
use Mockery;
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

    /** @test */
    public function reserved_tickets_are_released_when_a_reservation_is_cancelled(): void
    {
        $ticket1 = Mockery::mock(Ticket::class);
        $ticket1->shouldReceive('release')->once();

        $ticket2 = Mockery::mock(Ticket::class);
        $ticket2->shouldReceive('release')->once();

        $ticket3 = Mockery::mock(Ticket::class);
        $ticket3->shouldReceive('release')->once();

        $tickets = collect([$ticket1, $ticket2, $ticket3]);
        $reservation = new Reservation($tickets);

        $reservation->cancel();
    }
}
