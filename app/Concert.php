<?php

namespace App;

use App\Billing\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin IdeHelperConcert
 */
class Concert extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
        'published' => 'datetime',
        'ticket_price' => 'integer'
    ];

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
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'tickets');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // endregion Relationships

    // region Scopes
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    // endregion Scopes

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    public function publish(): self
    {
        $this->update(['published_at' => $this->freshTimestamp()]);

        $this->addTickets($this->ticket_quantity);

        return $this;
    }

    public function addTickets(int $quantity): Concert
    {
        for ($i = 0; $i < $quantity; $i++) {
            $this->tickets()->create(['code' => Str::random()]);
        }

        return $this;
    }

    public function reserveTickets(int $ticketQuantity, string $email): Reservation
    {
        $tickets = $this->findTickets($ticketQuantity)->each(
            function (Ticket $ticket) {
                $ticket->reserve();
            }
        );

        return new Reservation($tickets, $email);
    }

    public function findTickets(int $quantity): Collection
    {
        $tickets = $this->tickets()->available()->take($quantity)->get();

        if ($tickets->count() < $quantity) {
            throw new NotEnoughTicketsException(
                "A requested ticket quantity ({$quantity}) was for more than there are tickets remaining ({$tickets->count()})."
            );
        }

        return $tickets;
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
