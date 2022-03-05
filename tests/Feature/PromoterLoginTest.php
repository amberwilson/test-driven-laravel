<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PromoterLoginTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function logging_in_with_valid_credentials(): void
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create(
            [
                'email' => 'jane@example.com',
                'password' => bcrypt('super-secret-password')
            ]
        );

        $response = $this->post('/login',
        [
            'email' => 'jane@example.com',
            'password' => 'super-secret-password'
        ]);

        $response->assertRedirect('/backstage/concerts');
        self::assertTrue(Auth::check());
        self::assertTrue(Auth::user()->is($user));
    }

    /** @test */
    public function logging_in_with_invalid_credentials(): void
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create(
            [
                'email' => 'jane@example.com',
                'password' => bcrypt('super-secret-password')
            ]
        );

        $response = $this->post('/login',
                                [
                                    'email' => 'jane@example.com',
                                    'password' => 'bad-password'
                                ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        self::assertFalse(Auth::check());
    }

    /** @test */
    public function logging_in_with_an_account_that_does_not_exist(): void
    {
        $this->withoutExceptionHandling();

        $response = $this->post('/login',
                                [
                                    'email' => 'nobody@example.com',
                                    'password' => 'bad-password'
                                ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        self::assertFalse(Auth::check());
    }
}
