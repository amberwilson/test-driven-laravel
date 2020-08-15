<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model {
    protected $guarded = [];

    protected $casts = ['date' => 'datetime'];

    public function getFormattedDateAttribute(): string {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute(): string {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute(): string {
        return number_format($this->ticket_price / 100, 2);
    }
}
