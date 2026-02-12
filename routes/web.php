<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   return redirect('admin');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('test-mail', function() {
    $observation = \App\Models\Observation::with('pic', 'auditor', 'pic.department')->first();
    return $observation->toArray();
});
require __DIR__.'/settings.php';
