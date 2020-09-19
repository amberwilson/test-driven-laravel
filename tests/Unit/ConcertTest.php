<?php

namespace Tests\Unit;

use App\Billing\NotEnoughTicketsException;
use App\Concert;
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
    public function can_order_concert_tickets(): void
    {
        $concert = factory(Concert::class)->create()->addTickets(3);

        $order = $concert->orderTickets('jane@example.com', 3);

        self::assertEquals('jane@example.com', $order->fresh()->email);
        self::assertEquals(3, $order->ticketQuantity());
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
        $concert = factory(Concert::class)->create()->addTickets(50);

        $concert->orderTickets('jane@example.com', 30);

        self::assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    public function trying_to_purchase_more_tickets_than_remain_throws_an_exception(): void
    {
        $concert = factory(Concert::class)->create()->addTickets(10);

        try {
            $concert->orderTickets('jane@example.com', 11);
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(10, $concert->ticketsRemaining());
            self::assertFalse($concert->hasOrderFor('jane@example.com'));

            return;
        }

        self::fail('Order succeeded even though there were not enough tickets remaining.');
    }

    /** @test */
    public function cannot_order_tickets_that_have_already_been_purchased(): void
    {
        $concert = factory(Concert::class)->create()->addTickets(10);

        $concert->orderTickets('jane@example.com', 8);

        try {
            $concert->orderTickets('john@example.com', 3);
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(2, $concert->ticketsRemaining());
            self::assertFalse($concert->hasOrderFor('john@example.com'));

            return;
        }

        self::fail('Order succeeded even though the tickets were already purchased.');
    }

    /** @test */
    public function can_reserve_available_tickets(): void
    {
        /** @var Concert $concert */
        $concert = factory(Concert::class)->create()->addTickets(3);
        self::assertEquals(3, $concert->ticketsRemaining());

        $reservedTickets = $concert->reserveTickets(2);

        self::assertCount(2, $reservedTickets);
        self::assertEquals(1, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_purchased(): void
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $concert->orderTickets('jane@example.com', 2);

        try {
            $concert->reserveTickets(2);
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
        $concert->reserveTickets(2);

        try {
            $concert->reserveTickets(2);
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(1, $concert->ticketsRemaining());

            return;
        }

        self::fail('Reserving tickets succeeded even though tickets were already reserved.');
    }
}
