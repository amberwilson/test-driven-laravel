<?php


namespace Tests\Unit;

use App\Reservation;
use App\Ticket;
use Mockery;
use Mockery\Mock;
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

        $reservation = new Reservation($tickets, 'jane@example.com');

        self::assertEquals(3600, $reservation->totalCost());
    }

    /** @test */
    public function retrieving_the_reservations_tickets(): void
    {
        $tickets = collect(
            [
                (object)['price' => 1200],
                (object)['price' => 1200],
                (object)['price' => 1200],
            ]
        );

        $reservation = new Reservation($tickets, 'jane@example.com');

        self::assertSame($tickets, $reservation->tickets());
    }

    /** @test */
    public function retrieving_the_customers_email(): void
    {
        $reservation = new Reservation(collect(), 'jane@example.com');

        self::assertSame('jane@example.com', $reservation->email());
    }

    /** @test */
    public function reserved_tickets_are_released_when_a_reservation_is_cancelled(): void
    {
        $tickets = collect(
            [
                Mockery::spy(Ticket::class),
                Mockery::spy(Ticket::class),
                Mockery::spy(Ticket::class),
            ]
        );
        $reservation = new Reservation($tickets, 'jane@example.com');

        $reservation->cancel();

        /** @var Mock $ticket */
        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release')->once();
        }
    }
}
