<?php

namespace Tests\Feature;

use App\Invitation;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function viewing_an_unused_invitation(): void
    {
        $invitation = Invitation::factory()
            ->create([
                         'user_id' => null,
                         'code' => 'TESTCODE1234'
                     ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        self::assertTrue($response->data('invitation')->is($invitation));
    }

    /** @test */
    public function viewing_a_used_invitation(): void
    {
        Invitation::factory()
            ->create([
                         'user_id' => User::factory()->create(),
                         'code' => 'TESTCODE1234',
                     ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(404);
    }

    /** @test */
    public function viewing_an_invitation_that_does_not_exist(): void
    {
        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(404);
    }

    /** @test */
    public function registering_with_a_valid_invitation_code(): void
    {
        $invitation = Invitation::factory()
            ->create([
                         'user_id' => null,
                         'code' => 'TESTCODE1234'
                     ]);

        $response = $this->post(
            '/register',
            [
                'email' => 'jane@example.com',
                'password' => 'secret',
                'invitation_code' => 'TESTCODE1234'
            ]
        );

        $response->assertRedirect('/backstage/concerts');

        self::assertEquals(1, User::count());

        $user = User::first();
        $this->assertAuthenticatedAs($user);
        self::assertEquals('jane@example.com', $user->email);
        self::assertTrue(Hash::check('secret', $user->password));
        self::assertTrue($invitation->fresh()->user->is($user));
    }

    /** @test */
    public function registering_with_a_used_invitation_code(): void
    {
        Invitation::factory()
            ->create([
                         'user_id' => User::factory()->create(),
                         'code' => 'TESTCODE1234'
                     ]);
        self::assertEquals(1, User::count());

        $response = $this->post(
            '/register',
            [
                'email' => 'jane@example.com',
                'password' => 'secret',
                'invitation_code' => 'TESTCODE1234'
            ]
        );

        $response->assertStatus(404);
        self::assertEquals(1, User::count());
    }

    /** @test */
    public function registering_with_an_invitation_code_that_does_not_exist(): void
    {
        Invitation::factory()->create();

        $response = $this->post(
            '/register',
            [
                'email' => 'jane@example.com',
                'password' => 'secret',
                'invitation_code' => 'TESTCODE1234'
            ]
        );

        $response->assertStatus(404);
        self::assertEquals(0, User::count());
    }

    /** @test */
    public function email_is_required(): void
    {
        Invitation::factory()->create(['code' => 'TESTCODE1234']);

        $response = $this->from('/invitations/TESTCODE1234')
            ->post(
                '/register',
                [
                    'email' => '',
                    'password' => 'secret',
                    'invitation_code' => 'TESTCODE1234'
                ]
            );

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        self::assertEquals(0, User::count());
    }

    /** @test */
    public function email_must_be_an_email(): void
    {
        Invitation::factory()->create(['code' => 'TESTCODE1234']);

        $response = $this->from('/invitations/TESTCODE1234')
            ->post(
                '/register',
                [
                    'email' => 'notanemailaddress',
                    'password' => 'secret',
                    'invitation_code' => 'TESTCODE1234'
                ]
            );

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        self::assertEquals(0, User::count());
    }

    /** @test */
    public function email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'jane@example.com']);
        Invitation::factory()->create(['code' => 'TESTCODE1234']);

        self::assertEquals(1, User::count());

        $response = $this->from('/invitations/TESTCODE1234')
            ->post(
                '/register',
                [
                    'email' => 'jane@example.com',
                    'password' => 'secret',
                    'invitation_code' => 'TESTCODE1234'
                ]
            );

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        self::assertEquals(1, User::count());
    }

    /** @test */
    public function password_is_required(): void
    {
        Invitation::factory()->create(['code' => 'TESTCODE1234']);

        $response = $this->from('/invitations/TESTCODE1234')
            ->post(
                '/register',
                [
                    'email' => 'jane@example.com',
                    'password' => '',
                    'invitation_code' => 'TESTCODE1234'
                ]
            );

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('password');
        self::assertEquals(0, User::count());
    }
}
