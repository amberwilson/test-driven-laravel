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
        // Create a concert with a known date
        $concert = factory(Concert::class)->create(['date' => Carbon::parse('2020-08-15 8:00pm')]);

        // Retrieve formatted date
        $date = $concert->formatted_date;

        // Verify the date is formatted as expected
        self::assertEquals('August 15, 2020', $date);
    }
}
