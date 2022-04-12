<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConcertRequest;
use App\Http\Requests\UpdateConcertRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ConcertsController extends Controller
{
    public function index()
    {
        return view(
            'backstage.concerts.index',
            [
                'publishedConcerts' => Auth::user()->concerts->filter->isPublished(),
                'unpublishedConcerts' => Auth::user()->concerts->reject->isPublished(),
            ]
        );
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
        Auth::user()?->concerts()->create(
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
                'poster_image_path' => $request->poster_image?->store('posters', 'public'),
            ]
        );

        return redirect()->route('backstage.concerts.index');
    }

    public function edit($id)
    {
        /** @var Concert $concert */
        $concert = Auth::user()?->concerts()->findOrFail($id);

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
        $concert = Auth::user()?->concerts()->findOrFail($id);

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
