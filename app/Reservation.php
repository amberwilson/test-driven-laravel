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
}
