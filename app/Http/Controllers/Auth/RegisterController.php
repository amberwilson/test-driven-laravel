<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\AcceptInvitationRequest;
use App\Invitation;
use App\User;
use Illuminate\Support\Facades\Auth;

class RegisterController
{
    public function register(AcceptInvitationRequest $request)
    {
        $invitation = Invitation::findByCode(request('invitation_code'));

        abort_if($invitation->hasBeenUsed(), 404);

        $user = User::create(
            [
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]
        );

        $invitation->user()->associate($user)->save();

        Auth::login($user);

        return redirect()->route('backstage.concerts.index');
    }
}
