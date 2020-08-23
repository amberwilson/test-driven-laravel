<?php


namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Order extends Model
{
    protected $guarded = [];

    // region Relationships

    public function cancel()
    {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }

        $this->delete();
    }

    // endregion Relationships

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
