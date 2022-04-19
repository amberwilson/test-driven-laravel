<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForceStripeAccount
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Callable $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, callable $next)
    {
        if (Auth::user()->stripe_account_id === null) {
            return redirect()->route('backstage.stripe-connect.connect');
        }
        return $next($request);
    }
}
