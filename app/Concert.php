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
    // endregion Relationships

    // region Scopes
    public function scopePublished($query) {
        return $query->whereNotNull('published_at');
    }
    // endregion Scopes
}
