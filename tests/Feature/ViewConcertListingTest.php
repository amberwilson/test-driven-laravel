<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    public function user_can_view_a_published_concert_listing(): void
    {
        $concert = factory(Concert::class)->state('published')->create(
            [
                'title' => 'The Red Chord',
                'subtitle' => 'with Animosity and Lethargy',
                'date' => Carbon::parse('August 15, 2020 8:00pm'),
                'ticket_price' => 3250,
                'venue' => 'The Mosh Pit',
                'venue_address' => '123 Example Lane',
                'city' => 'Laraville',
                'state' => 'NS',
                'zip' => '17916',
                'additional_information' => 'For tickets, call (555) 555-5555.',
            ]
        );

        $response = $this->get("/concerts/{$concert->id}");

        $response->assertSee('The Red Chord');
        $response->assertSee('with Animosity and Lethargy');
        $response->assertSee('August 15, 2020');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('The Mosh Pit');
        $response->assertSee('123 Example Lane');
        $response->assertSee('Laraville, NS 17916');
        $response->assertSee('For tickets, call (555) 555-5555.');
    }

    /** @test */
    public function user_cannot_view_unpublished_concert_listings(): void
    {
        $concert = factory(Concert::class)->states('unpublished')->create();

        $this->withExceptionHandling();
        $response = $this->get("/concerts/{$concert->id}");

        $response->assertStatus(404);
    }
}
