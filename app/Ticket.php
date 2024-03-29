<?php


namespace App;

use App\Facades\TicketCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @mixin Builder
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin IdeHelperTicket
 */
class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'concert_id' => 'integer'
    ];

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

    public function scopeSold($query)
    {
        return $query->whereNotNull('order_id');
    }

    // endregion Scopes

    public function release(): void
    {
        $this->update(['reserved_at' => null]);
    }

    public function claimFor(Order $order): void
    {
        $this->code = TicketCode::generateFor($this);
        $order->tickets()->save($this);
    }

    public function reserve(): void
    {
        $this->reserved_at = now();
        $this->save();
    }
}
