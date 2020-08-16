<?php

namespace Tests\Unit;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ConcertTest extends TestCase {
    use DatabaseMigrations;

    /** @test */
    public function can_get_formatted_date(): void {
        $concert = factory(Concert::class)->make(['date' => Carbon::parse('2020-08-15 8:00pm')]);

        self::assertEquals('August 15, 2020', $concert->formatted_date);
    }

    /** @test */
    public function can_get_formatted_start_time(): void {
        $concert = factory(Concert::class)->make(['date' => Carbon::parse('2020-08-15 21:30:00')]);

        self::assertEquals('9:30pm', $concert->formatted_start_time);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars(): void {
        $concert = factory(Concert::class)->make(['ticket_price' => 123456]);

        self::assertEquals('1,234.56', $concert->ticket_price_in_dollars);
    }

    /** @test */
    public function concerts_with_a_published_at_date_are_published(): void {
        $publishedConcertA  = factory(Concert::class)->states('published')->create();
        $publishedConcertB  = factory(Concert::class)->states('published')->create();
        $unpublishedConcert = factory(Concert::class)->states('unpublished')->create();

        $publishedConcerts = Concert::published()->get();

        self::assertTrue($publishedConcerts->contains($publishedConcertA));
        self::assertTrue($publishedConcerts->contains($publishedConcertB));
        self::assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */
    public function can_order_concert_tickets(): void {
        $concert = factory(Concert::class)->create();

        $order = $concert->orderTickets('jane@example.com', 3);

        self::assertEquals('jane@example.com', $order->email);
        self::assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    public function can_add_tickets(): void {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(50);

        self::assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order(): void {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(50);
        $order = $concert->orderTickets('jane@example.com', 30);

        self::assertEquals(20, $concert->ticketsRemaining());
    }
}
