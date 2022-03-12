<?php

namespace Tests\Feature\Backstage;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

use function bcrypt;
use function factory;

class PromoterLoginTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function logging_in_with_valid_credentials(): void
    {
        $user = factory(User::class)->create(
            [
                'email' => 'jane@example.com',
                'password' => bcrypt('super-secret-password')
            ]
        );

        $response = $this->post(
            '/login',
            [
                'email' => 'jane@example.com',
                'password' => 'super-secret-password'
            ]
        );

        $response->assertRedirect('/backstage/concerts/new');
        self::assertTrue(Auth::check());
        self::assertTrue(Auth::user()->is($user));
    }

    /** @test */
    public function logging_in_with_invalid_credentials(): void
    {
        $user = factory(User::class)->create(
            [
                'email' => 'jane@example.com',
                'password' => bcrypt('super-secret-password')
            ]
        );

        $response = $this->post(
            '/login',
            [
                'email' => 'jane@example.com',
                'password' => 'bad-password'
            ]
        );

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        self::assertFalse(Auth::check());
    }

    /** @test */
    public function logging_in_with_an_account_that_does_not_exist(): void
    {
        $response = $this->post(
            '/login',
            [
                'email' => 'nobody@example.com',
                'password' => 'bad-password'
            ]
        );

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        self::assertTrue(session()->hasOldInput('email'));
        self::assertFalse(session()->hasOldInput('password'));
        self::assertFalse(Auth::check());
    }

    /** @test */
    public function logging_out_the_current_user(): void
    {
        Auth::login(factory(User::class)->create());

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        self::assertFalse(Auth::check());
    }
}
