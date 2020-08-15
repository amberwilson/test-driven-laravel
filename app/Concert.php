<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model {
    protected $guarded = [];

    protected $casts = ['date' => 'datetime'];

    public function getFormattedDateAttribute(): string {
        return $this->date->format('F j, Y');
    }
}
