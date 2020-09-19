<?php


namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Ticket extends Model
{
    protected $guarded = [];

    // region Accessors & Mutators
    public function getPriceAttribute()
    {
        return $this->concert->ticket_price;
    }
    // endregion Accessors & Mutators

    // region Relationships
    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }
    // endregion Relationships

    // region Scopes
    public function scopeAvailable($query)
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    // endregion Scopes

    public function release()
    {
        $this->update(['order_id' => null]);
    }

    public function reserve()
    {
        $this->reserved_at = now();
        $this->save();
    }
}
