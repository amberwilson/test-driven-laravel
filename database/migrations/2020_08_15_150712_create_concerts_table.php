<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConcertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         *   ['title'                  => 'The Red Chord',
        'subtitle'               => 'with Animosity and Lethargy',
        'date'                   => Carbon::parse('August 15, 2020 8:00pm'),
        'ticket_price'           => 3250,
        'venue'                  => 'The Mosh Pit',
        'venue_address'          => '123 Example Lane',
        'city'                   => 'Laraville',
        'state'                  => 'NS',
        'zip'                    => '17916',
        'additional_information' => 'For tickets, call (555) 555-5555.',]
         */

        Schema::create('concerts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle');
            $table->dateTime('date');
            $table->integer('ticket_price');
            $table->string('venue');
            $table->string('venue_address');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('additional_information');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('concerts');
    }
}
