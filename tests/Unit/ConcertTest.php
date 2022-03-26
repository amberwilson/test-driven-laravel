<?php

namespace Tests\Unit;

use App\Billing\NotEnoughTicketsException;
use App\Concert;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_get_formatted_date(): void
    {
        $concert = factory(Concert::class)->make(['date' => Carbon::parse('2020-08-15 8:00pm')]);

        self::assertEquals('August 15, 2020', $concert->formatted_date);
    }

    /** @test */
    public function can_get_formatted_start_time(): void
    {
        $concert = factory(Concert::class)->make(['date' => Carbon::parse('2020-08-15 21:30:00')]);

        self::assertEquals('9:30pm', $concert->formatted_start_time);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars(): void
    {
        $concert = factory(Concert::class)->make(['ticket_price' => 123456]);

        self::assertEquals('1,234.56', $concert->ticket_price_in_dollars);
    }

    /** @test */
    public function concerts_with_a_published_at_date_are_published(): void
    {
        $publishedConcertA = factory(Concert::class)->states('published')->create();
        $publishedConcertB = factory(Concert::class)->states('published')->create();
        $unpublishedConcert = factory(Concert::class)->states('unpublished')->create();

        $publishedConcerts = Concert::published()->get();

        self::assertTrue($publishedConcerts->contains($publishedConcertA));
        self::assertTrue($publishedConcerts->contains($publishedConcertB));
        self::assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */
    public function concerts_can_be_published(): void
    {
        $concert = factory(Concert::class)->create(
            [
                'published_at' => null,
                'ticket_quantity' => 5,
            ]
        );

        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());

        $concert->publish();

        $this->assertTrue($concert->isPublished());
        $this->assertEquals(5, $concert->ticketsRemaining());
    }

    /** @test */
    public function can_add_tickets(): void
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(50);

        self::assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order(): void
    {
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 30)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 20)->create(['order_id' => null]));

        self::assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    public function trying_to_reserve_more_tickets_than_remain_throws_an_exception(): void
    {
        $concert = factory(Concert::class)->create()->addTickets(10);

        try {
            $concert->reserveTickets(11, 'jane@example.com');
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(10, $concert->ticketsRemaining());
            self::assertFalse($concert->hasOrderFor('jane@example.com'));

            return;
        }

        self::fail('Order succeeded even though there were not enough tickets remaining.');
    }

    /** @test */
    public function can_reserve_available_tickets(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(3);
        self::assertEquals(3, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2, 'jane@example.com');

        self::assertCount(2, $reservation->tickets());
        self::assertSame('jane@example.com', $reservation->email());
        self::assertEquals(1, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_purchased(): void
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $order = factory(Order::class)->create();
        $order->tickets()->saveMany($concert->tickets->take(2));

        try {
            $concert->reserveTickets(2, 'jane@example.com');
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(1, $concert->ticketsRemaining());

            return;
        }

        self::fail('Reserving tickets succeeded even though tickets were already sold.');
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_reserved(): void
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $concert->reserveTickets(2, 'jane@example.com');

        try {
            $concert->reserveTickets(2, 'jane@example.com');
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(1, $concert->ticketsRemaining());

            return;
        }

        self::fail('Reserving tickets succeeded even though tickets were already reserved.');
    }
}
