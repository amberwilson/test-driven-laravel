<?php

namespace Tests\Unit\Mail;

use App\AttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Tests\TestCase;

class AttendeeMessageEmailTest extends TestCase
{
    /** @test */
    public function email_has_the_correct_subject_and_message(): void
    {
        $message = new AttendeeMessage(['subject' => 'My Subject', 'message' => 'My message']);
        $email = new AttendeeMessageEmail($message);

        self::assertEquals('My Subject', $email->build()->subject);
        self::assertEquals('My message', trim($this->render($email)));
    }

    private function render(AttendeeMessageEmail $mailable)
    {
        $mailable->build();

        return view($mailable->textView, $mailable->buildViewData())->render();
    }
}
