<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use Auth;
use Stripe\OAuth;
use Stripe\Stripe;

class StripeConnectController extends Controller
{
    public function authorizeRedirect()
    {
        $params = [
            'response_type' => 'code',
            'scope' => 'read_write',
            'client_id' => config('services.stripe.client_id'),
        ];

        return redirect(
            'https://connect.stripe.com/oauth/v2/authorize?' . http_build_query($params)
        );
    }

    function redirect()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $accessTokenResponse = OAuth::token(
            [
                'grant_type' => 'authorization_code',
                'code' => request('code'),
            ]
        );

        Auth::user()?->update(
            [
                'stripe_account_id' => $accessTokenResponse->stripe_user_id,
                'stripe_access_token' => $accessTokenResponse->access_token,
            ]
        );

        return redirect()->route('backstage.concerts.index');
    }
}
