<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Backstage\ConcertMessagesController;
use App\Http\Controllers\Backstage\ConcertsController;
use App\Http\Controllers\Backstage\PublishedConcertOrdersController;
use App\Http\Controllers\Backstage\PublishedConcertsController;
use App\Http\Controllers\ConcertOrdersController;
use App\Http\Controllers\ConcertsController as PublicConcertsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', static function () {
    if (Auth::user()) {
        return redirect()->route('backstage.concerts.index');
    }
    return redirect()->route('auth.show-login');
});

Route::get('/concerts/{id}', [PublicConcertsController::class, 'show'])->name('concerts.show');
Route::post('/concerts/{id}/orders', [ConcertOrdersController::class, 'store']);
Route::get('/orders/{confirmationNumber}', [ConcertOrdersController::class, 'show']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('auth.show-login');
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');

Route::group(['middleware' => 'auth', 'prefix' => 'backstage'], static function () {
    Route::get('/concerts/new', [ConcertsController::class, 'create'])->name('backstage.concerts.new');
    Route::get('/concerts', [ConcertsController::class, 'index'])->name('backstage.concerts.index');
    Route::post('/concerts', [ConcertsController::class, 'store']);
    Route::get('/concerts/{id}/edit', [ConcertsController::class, 'edit'])->name('backstage.concerts.edit');
    Route::patch('/concerts/{id}', [ConcertsController::class, 'update'])->name('backstage.concerts.update');

    Route::post('/published-concerts', [PublishedConcertsController::class, 'store'])->name(
        'backstage.published-concerts.store'
    );
    Route::get('/published-concerts/{id}/orders', [PublishedConcertOrdersController::class, 'index'])->name(
        'backstage.published-concert-orders.index'
    );

    Route::get('/concerts/{id}/messages/new', [ConcertMessagesController::class, 'create'])->name(
        'backstage.concert-messages.new'
    );
    Route::post('/concerts/{id}/messages', [ConcertMessagesController::class, 'store'])->name(
        'backstage.concert-messages.store'
    );
});

