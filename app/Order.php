<?php


namespace App;

use App\Billing\Charge;
use App\Facades\OrderConfirmationNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

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
            'amount' => $this->amount,
            'tickets' => $this->tickets->map(function ($ticket) {
                return ['code' => $ticket->code];
            })->all(),
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

        $tickets->each->claimFor($order);

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
