<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Concert extends Model {
    protected $guarded = [];

    protected $casts = ['date' => 'datetime'];

    // region Accessors & Mutators
    public function getFormattedDateAttribute(): string {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute(): string {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute(): string {
        return number_format($this->ticket_price / 100, 2);
    }
    // endregion Accessors & Mutators

    // region Relationships
    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function tickets() {
        return $this->hasMany(Ticket::class);
    }
    // endregion Relationships

    // region Scopes
    public function scopePublished($query) {
        return $query->whereNotNull('published_at');
    }

    // endregion Scopes

    public function orderTickets(string $email, int $ticketQuantity) {
        $order   = $this->orders()->create(['email' => $email]);
        $tickets = $this->tickets()->take($ticketQuantity)->get();

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function addTickets(int $quantity) {
        for ($i = 0; $i < $quantity; $i++) {
            $this->tickets()->create([]);
        }
    }

    public function ticketsRemaining() {
        return $this->tickets()->whereNull('order_id')->count();
    }
}
