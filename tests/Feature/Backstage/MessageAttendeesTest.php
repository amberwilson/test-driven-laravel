<?php

namespace Tests\Feature\Backstage;

use App\AttendeeMessage;
use App\Concert;
use App\Jobs\SendAttendeeMessage;
use App\User;
use ConcertFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Queue;
use Tests\TestCase;

class MessageAttendeesTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function a_promoter_can_view_the_message_form_for_their_own_concert()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished(
            [
                'user_id' => $user->id,
            ]
        );

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert-messages.new');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function a_promoter_cannot_view_the_message_form_for_another_concert()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished(
            [
                'user_id' => factory(User::class)->create(),
            ]
        );

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(404);
    }

    /** @test */
    function a_guest_cannot_view_the_message_form_for_any_concert()
    {
        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertRedirect('/login');
    }

    /** @test */
    public function a_promoter_can_send_a_new_message(): void
    {
        Queue::fake();

        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished(
            [
                'user_id' => $user->id,
            ]
        );

        $response = $this->actingAs($user)->post(
            "/backstage/concerts/{$concert->id}/messages",
            [
                'subject' => 'My Subject',
                'message' => 'My message',
            ]
        );

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHas('flash');

        $message = AttendeeMessage::first();
        self::assertEquals($concert->id, $message->concert_id);
        self::assertEquals('My Subject', $message->subject);
        self::assertEquals('My message', $message->message);

        Queue::assertPushed(
            SendAttendeeMessage::class,
            static function (SendAttendeeMessage $job) use ($message) {
                return $job->attendeeMessage->is($message);
            }
        );
    }

    /** @test */
    function a_promoter_cannot_send_a_new_message_for_other_concerts()
    {
        Queue::fake();

        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished(
            [
                'user_id' => factory(User::class)->create(),
            ]
        );

        $response = $this->actingAs($user)->post(
            "/backstage/concerts/{$concert->id}/messages",
            [
                'subject' => 'My Subject',
                'message' => 'My message',
            ]
        );

        $response->assertStatus(404);

        self::assertEquals(0, AttendeeMessage::count());

        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    function a_guest_cannot_send_a_new_message_for_any_concerts()
    {
        Queue::fake();

        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished();

        $response = $this->post(
            "/backstage/concerts/{$concert->id}/messages",
            [
                'subject' => 'My Subject',
                'message' => 'My message',
            ]
        );

        $response->assertRedirect('/login');

        self::assertEquals(0, AttendeeMessage::count());

        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    function subject_is_required()
    {
        Queue::fake();

        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished(
            [
                'user_id' => $user->id,
            ]
        );

        $response = $this->from("/backstage/concerts/{$concert->id}/messages/new")
            ->actingAs($user)->post(
                "/backstage/concerts/{$concert->id}/messages",
                [
                    'subject' => '',
                    'message' => 'My message',
                ]
            );

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHasErrors('subject');
        self::assertEquals(0, AttendeeMessage::count());

        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    function message_is_required()
    {
        Queue::fake();

        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished(
            [
                'user_id' => $user->id,
            ]
        );

        $response = $this->from("/backstage/concerts/{$concert->id}/messages/new")
            ->actingAs($user)->post(
                "/backstage/concerts/{$concert->id}/messages",
                [
                    'subject' => 'My Subject',
                    'message' => '',
                ]
            );

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHasErrors('message');
        self::assertEquals(0, AttendeeMessage::count());

        Queue::assertNotPushed(SendAttendeeMessage::class);
    }
}
