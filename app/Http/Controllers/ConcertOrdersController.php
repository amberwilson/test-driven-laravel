<?php

namespace App\Http\Controllers;

use App\Billing\NotEnoughTicketsException;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Order;
use App\Reservation;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(
            request(),
            [
                'email' => 'required|email',
                'ticket_quantity' => 'required|numeric|min:1',
                'payment_token' => 'required',
            ]
        );

        try {
            $tickets = $concert->reserveTickets(request('ticket_quantity'));
            $reservation = new Reservation($tickets);

            $this->paymentGateway->charge($reservation->totalCost(), request('payment_token'));

            $order = Order::forTickets(request('email'), $tickets, $reservation->totalCost());

            return response()->json($order, 201);
        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
