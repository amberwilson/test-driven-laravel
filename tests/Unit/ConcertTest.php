<?php

namespace Tests\Unit;

use App\Billing\NotEnoughTicketsException;
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
        $concert->addTickets(3);

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

    /** @test */
    public function trying_to_purchase_more_tickets_than_remain_throws_an_exception(): void {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);

        try {
            $concert->orderTickets('jane@example.com', 11);
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(10, $concert->ticketsRemaining());

            $order = $concert->orders()->where('email', 'jane@example.com')->first();
            self::assertNull($order);

            return;
        }

        self::fail('Order succeeded even though there were not enough tickets remaining.');
    }

    /** @test */
    public function cannot_order_tickets_that_have_already_been_purchased(): void {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);

        $concert->orderTickets('jane@example.com', 8);

        try {
            $concert->orderTickets('john@example.com', 3);
        } catch (NotEnoughTicketsException $e) {
            self::assertEquals(2, $concert->ticketsRemaining());

            $johnsOrder = $concert->orders()->where('email', 'john@example.com')->first();
            self::assertNull($johnsOrder);

            return;
        }

        self::fail('Order succeeded even though the tickets were already purchased.');
    }
}
