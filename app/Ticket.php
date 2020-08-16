<?php


namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Ticket extends Model {
    // region Scopes
    public function scopeAvailable($query) {
        return $query->whereNull('order_id');
    }
    // endregion Scopes
}
