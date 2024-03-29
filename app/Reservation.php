<?php


namespace App;


use App\Billing\PaymentGateway;

class Reservation
{
    private $tickets;
    private string $email;

    public function __construct($tickets, string $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }


    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function complete(PaymentGateway $paymentGateway, string $token, string $destinationAccountId): Order
    {
        $charge = $paymentGateway->charge($this->totalCost(), $token, $destinationAccountId);

        return Order::forTickets($this->email(), $this->tickets(), $charge);
    }

    public function cancel(): void
    {
        /** @var Ticket $ticket */
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }
    }
}
