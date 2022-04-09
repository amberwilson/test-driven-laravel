<?php

namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

class PromoterLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function logging_in_with_valid_credentials()
    {
        $user = User::factory()->create(
            [
                'email' => 'jane@example.com',
                'password' => bcrypt('super-secret-password'),
            ]
        );

        $this->browse(function ($browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'super-secret-password')
                ->press('Log in')
                ->assertPathIs('/backstage/concerts');
        });
    }

    /** @test */
    public function logging_in_with_invalid_credentials()
    {
        $user = User::factory()->create(
            [
                'email' => 'jane@example.com',
                'password' => bcrypt('super-secret-password'),
            ]
        );

        $this->browse(function ($browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'bad-password')
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertSee('credentials do not match');
        });
    }
}
