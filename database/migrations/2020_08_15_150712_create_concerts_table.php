<?php

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
        Schema::create(
            'concerts',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->dateTime('date');
                $table->integer('ticket_price');
                $table->integer('ticket_quantity');
                $table->string('venue');
                $table->string('venue_address');
                $table->string('city');
                $table->string('state');
                $table->string('zip');
                $table->string('additional_information')->nullable();
                $table->string('poster_image_path')->nullable();
                $table->dateTime('published_at')->nullable();
                $table->timestamps();
            }
        );
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
