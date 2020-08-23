<?php

namespace App;

use App\Billing\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Concert extends Model
{
    protected $guarded = [];

    protected $casts = ['date' => 'datetime'];

    // region Accessors & Mutators
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute(): string
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute(): string
    {
        return number_format($this->ticket_price / 100, 2);
    }
    // endregion Accessors & Mutators

    // region Relationships

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function orderTickets(string $email, int $ticketQuantity)
    {
        $tickets = $this->tickets()->available()->take($ticketQuantity)->get();

        if ($tickets->count() < $ticketQuantity) {
            throw new NotEnoughTicketsException(
                "A requested ticket quantity ({$ticketQuantity}) was for more than there are tickets remaining ({$tickets->count()})."
            );
        }

        $order = $this->orders()->create(['email' => $email]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }
    // endregion Relationships

    // region Scopes

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // endregion Scopes

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addTickets(int $quantity)
    {
        for ($i = 0; $i < $quantity; $i++) {
            $this->tickets()->create([]);
        }

        return $this;
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    public function hasOrderFor(string $customersEmail): bool
    {
        return $this->orders()->where('email', $customersEmail)->count() > 0;
    }

    public function ordersFor(string $customersEmail)
    {
        return $this->orders()->where('email', $customersEmail);
    }
}
