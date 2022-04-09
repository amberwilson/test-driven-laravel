<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendeesMessageRequest;
use App\Jobs\SendAttendeeMessage;
use Illuminate\Support\Facades\Auth;

class ConcertMessagesController extends Controller
{
    public function create(int $id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        return view('backstage.concert-messages.new', ['concert' => $concert]);
    }

    public function store(StoreAttendeesMessageRequest $request, int $id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        $message = $concert->attendeeMessages()->create($request->all(['subject', 'message']));

        SendAttendeeMessage::dispatch($message);

        return redirect()->route('backstage.concert-messages.new', $concert)
            ->with(
                'flash',
                'Your message has been sent.'
            );
    }
}
