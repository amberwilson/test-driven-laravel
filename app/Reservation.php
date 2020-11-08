<?php


namespace App;


class Reservation
{
    private $tickets;

    /**
     * Reservation constructor.
     * @param $tickets
     */
    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }


    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function cancel(): void
    {
        /** @var Ticket $ticket */
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }
    }
}
