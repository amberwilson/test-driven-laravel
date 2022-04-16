<?php

namespace App;

use App\Mail\InvitationEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

/**
 * @mixin IdeHelperInvitation
 */
class Invitation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function findByCode(string $code): Model|Builder|Invitation
    {
        return self::where('code', $code)->firstOrFail();
    }

    public function hasBeenUsed(): bool
    {
        return $this->user_id !== null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function send(): void
    {
        Mail::to($this->email)->send(new InvitationEmail($this));
    }
}
