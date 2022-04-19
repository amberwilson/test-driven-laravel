<?php

namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
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

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->typeSlowly('email', $user->email)
                ->typeSlowly('password', 'super-secret-password')
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

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'bad-password')
                ->press('Log in')
                ->assertPathIs('/login')
                ->screenshot('logging_in_with_invalid_credentials')
                ->assertSee('credentials do not match');
        });
    }
}
