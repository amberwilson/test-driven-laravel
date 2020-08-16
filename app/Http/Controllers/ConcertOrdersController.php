<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Concert;

class ConcertOrdersController extends Controller {
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway) {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId) {
        $concert = Concert::published()->findOrFail($concertId);

        $ticketQuantity = request('ticket_quantity');
        $amount         = $ticketQuantity * $concert->ticket_price;
        $token          = request('payment_token');
        $this->paymentGateway->charge($amount, $token);

        $order = $concert->orders()->create(['email' => request('email')]);

        for ($i = 0; $i < $ticketQuantity; $i++) {
            $order->tickets()->create([]);
        }

        return response()->json([], 201);
    }
}
