<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ConcertOrdersController;
use App\Http\Controllers\ConcertsController;
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

Route::get('/concerts/{id}', [ConcertsController::class, 'show'])->name('concerts.show');
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
});

