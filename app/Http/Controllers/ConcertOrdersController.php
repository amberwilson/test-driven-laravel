<?php

namespace App\Http\Controllers;

use App\Billing\NotEnoughTicketsException;
use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Mail\OrderConfirmationEmail;
use App\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        /** @var Concert $concert */
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
            $reservation = $concert->reserveTickets(request('ticket_quantity'), request('email'));

            $order = $reservation->complete(
                $this->paymentGateway,
                request('payment_token'),
                $concert->user->stripe_account_id
            );

            Mail::to($order->email)->send(new OrderConfirmationEmail($order));

            return response()->json($order, 201);
        } catch (PaymentFailedException $e) {
            Log::error($e);
            $reservation->cancel();
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            Log::error($e);
            return response()->json([], 422);
        }
    }

    public function show($confirmationNumber)
    {
        $order = Order::findByConfirmationNumber($confirmationNumber);

        return view('orders.show', ['order' => $order]);
    }
}
