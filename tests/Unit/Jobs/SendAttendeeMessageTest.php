<?php

namespace Tests\Unit\Jobs;

use App\AttendeeMessage;
use App\Concert;
use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Database\Factories\ConcertFactory;
use Database\Factories\OrderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendAttendeeMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_the_message_to_all_concert_attendees(): void
    {
        Mail::fake();

        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished();
        /** @var Concert $otherConcert */
        $otherConcert = ConcertFactory::createPublished();
        /** @var AttendeeMessage $message */
        $message = AttendeeMessage::create(
            [
                'concert_id' => $concert->id,
                'subject' => 'My Subject',
                'message' => 'My message',
            ]
        );

        $orderA = OrderFactory::createForConcert(
            $concert,
            ['email' => 'alex@example.com']
        );
        $orderB = OrderFactory::createForConcert(
            $concert,
            ['email' => 'barb@example.com']
        );
        $orderC = OrderFactory::createForConcert(
            $concert,
            ['email' => 'claire@example.com']
        );

        $orderOrder = OrderFactory::createForConcert(
            $otherConcert,
            ['email' => 'ortho@example.com']
        );

        SendAttendeeMessage::dispatch($message);

        Mail::assertQueued(
            AttendeeMessageEmail::class,
            static function (AttendeeMessageEmail $mail) use ($message) {
                return $mail->hasTo('alex@example.com') && $mail->attendeeMessage->is($message);
            }
        );
        Mail::assertQueued(
            AttendeeMessageEmail::class,
            static function (AttendeeMessageEmail $mail) use ($message) {
                return $mail->hasTo('barb@example.com') && $mail->attendeeMessage->is($message);
            }
        );
        Mail::assertQueued(
            AttendeeMessageEmail::class,
            static function (AttendeeMessageEmail $mail) use ($message) {
                return $mail->hasTo('claire@example.com') && $mail->attendeeMessage->is($message);
            }
        );

        Mail::assertNotQueued(
            AttendeeMessageEmail::class,
            static function (AttendeeMessageEmail $mail) {
                return $mail->hasTo('ortho@example.com');
            }
        );
    }
}
