<?php

namespace App;

use Hashids\Hashids;

class HashidsTicketCodeGenerator implements TicketCodeGenerator
{
    private HashIds $hashids;

    public function __construct(string $salt)
    {
        $this->hashids = new Hashids($salt, 6, 'ABCDEFGHIJLMNOPQRSTUVWXYZ');
    }

    public function generateFor(Ticket $ticket): string
    {
        return $this->hashids->encode($ticket->id);
    }
}
