<?php

namespace App\Http\Controllers;

use App\Concert;
use App\Http\Requests\StoreConcertRequest;
use App\Http\Requests\UpdateConcertRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ConcertsController extends Controller
{
    public function index()
    {
        return view('backstage.concerts.index', ['concerts' => Auth::user()->concerts]);
    }

    public function show(int $id)
    {
        $concert = Concert::published()->findOrFail($id);

        return view('concerts.show', ['concert' => $concert]);
    }

    public function create()
    {
        return view('backstage.concerts.create');
    }

    public function store(StoreConcertRequest $request)
    {
        $concert = Auth::user()->concerts()->create(
            [
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'additional_information' => $request->additional_information,
                'date' => Carbon::parse(vsprintf('%s %s', [$request->date, $request->time])),
                'venue' => $request->venue,
                'venue_address' => $request->venue_address,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'ticket_price' => $request->ticket_price * 100,
                'ticket_quantity' => (int)$request->ticket_quantity,
            ]
        );

        $concert->publish();

        return redirect()->route('concerts.show', $concert);
    }

    public function edit($id)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail($id);

        abort_if($concert->isPublished(), 403);

        return view(
            'backstage.concerts.edit',
            [
                'concert' => $concert,
            ]
        );
    }

    public function update(UpdateConcertRequest $request, int $id)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail($id);

        abort_if($concert->isPublished(), 403);

        $concert->update(
            [
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'additional_information' => $request->additional_information,
                'date' => Carbon::parse(vsprintf('%s %s', [$request->date, $request->time])),
                'venue' => $request->venue,
                'venue_address' => $request->venue_address,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'ticket_price' => $request->ticket_price * 100,
                'ticket_quantity' => (int)$request->ticket_quantity,
            ]
        );

        return redirect()->route('backstage.concerts.index');
    }
}
