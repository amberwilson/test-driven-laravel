<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;

class LoginController
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login()
    {
        if (Auth::attempt(request(['email', 'password']))) {
            return redirect('/backstage/concerts');
        }

        return redirect('/login')->withErrors(
            [
                'email' => 'These credentials do not match our records.'
            ],
        );
    }
}
