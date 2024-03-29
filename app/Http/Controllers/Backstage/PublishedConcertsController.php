<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use Auth;

class PublishedConcertsController extends Controller
{
    public function store()
    {
        $concert = Auth::user()->concerts()->findOrFail(request('concert_id'));

        abort_if($concert->isPublished(), 422);

        $concert->publish();

        return redirect()->route('backstage.concerts.index');
    }
}
