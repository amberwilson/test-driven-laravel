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
        $publishedConcertA  = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 day')]);
        $publishedConcertB  = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 day')]);
        $unpublishedConcert = factory(Concert::class)->create(['published_at' => null]);

        $publishedConcerts = Concert::published()->get();

        self::assertTrue($publishedConcerts->contains($publishedConcertA));
        self::assertTrue($publishedConcerts->contains($publishedConcertB));
        self::assertFalse($publishedConcerts->contains($unpublishedConcert));
    }
}
