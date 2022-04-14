<?php

namespace App\Events;

use App\Concert;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConcertAdded
{
    use Dispatchable, SerializesModels;

    public Concert $concert;

    public function __construct(Concert $concert)
    {
        $this->concert = $concert;
    }
}
