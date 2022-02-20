<?php


namespace App;

use App\Billing\Charge;
use App\Facades\OrderConfirmationNumber;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin IdeHelperOrder
 */
class Order extends Model
{
    protected $guarded = [];
    protected $casts = [
        'amount' => 'integer'
    ];

    public function toArray(): array
    {
        return [
            'confirmation_number' => $this->confirmation_number,
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount,
        ];
    }

    // region Relationships
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    // endregion Relationships

    public static function forTickets(string $email, Collection $tickets, Charge $charge): self
    {
        $order = (new self())::create(
            [
                'confirmation_number' => OrderConfirmationNumber::generate(),
                'email' => $email,
                'amount' => $charge->amount(),
                'card_last_four' => $charge->cardLastFour(),
            ]
        );

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public static function findByConfirmationNumber(string $confirmationNumber): ?Order
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }

    public function ticketQuantity(): int
    {
        return $this->tickets()->count();
    }
}
