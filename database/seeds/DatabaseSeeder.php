<?php

use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('published')->create(
            [
                'user_id' => $user->id,
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
        $concert->addTickets(10);

        /** @var Concert $concert */
        $concert = factory(Concert::class)->state('unpublished')->create(
            [
                'user_id' => $user->id,
                'title' => 'The Red Chord 2',
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
        $concert->addTickets(30);
    }
}
