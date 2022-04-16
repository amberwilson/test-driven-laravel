<?php

namespace Tests\Unit\Mail;

use App\Invitation;
use App\Mail\InvitationEmail;
use Tests\TestCase;

class InvitationEmailTest extends TestCase
{
    /** @test */
    public function email_contains_a_link_to_accept_the_invitation(): void
    {
        $invitation = Invitation::factory()->make(
            [
                'email' => 'jane@example.com',
                'code' => 'TESTCODE1234'
            ]
        );
        $email = new InvitationEmail($invitation);

        self::assertStringContainsString(
            url('/invitations/TESTCODE1234'),
            $email->render()
        );
    }

    /** @test */
    public function email_has_the_correct_subject(): void
    {
        $invitation = Invitation::factory()->make();
        $email = new InvitationEmail($invitation);

        self::assertEquals(
            "You're invited to join TicketBeast",
            $email->build()->subject
        );
    }
}
