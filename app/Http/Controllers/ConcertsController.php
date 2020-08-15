<?php

namespace App\Http\Controllers;

use App\Concert;

class ConcertsController extends Controller {
    public function show(int $id) {
        $concert = Concert::whereNotNull('published_at')->findOrFail($id);

        return view('concerts.show', ['concert' => $concert]);
    }
}
