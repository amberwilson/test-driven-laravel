<?php


namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin IdeHelperOrder
 */
class Order extends Model
{
    protected $guarded = [];

    public function toArray()
    {
        return [
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

    public static function forTickets(string $email, Collection $tickets, int $amount): self
    {
        $order = (new self())->create(
            [
                'email' => $email,
                'amount' => $amount,
            ]
        );

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }
}
