<?php

use Illuminate\Support\Facades\Route;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;


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

// Route::get('ai-test', function() {
//     try {
//         $analytics = [
//   "period" => "January 2026",
//   "total" => 120,
//   "pending" => 35,
//   "resolved" => 80,
//   "for_discussion" => 5,
//   "resolution_rate" => 66.7,
//   "avg_resolution_days" => 12,
//   "top_departments" => [
//     ["name" => "MIS", "total" => 40],
//     ["name" => "Service", "total" => 30]
//   ],
//   "trend_vs_last_month" => [
//     "total_change_percent" => 10,
//     "pending_change_percent" => -5
//   ]
// ];

// $prompt = "
// You are an internal audit analytics assistant.

// Analyze the following JSON data and generate a professional executive summary.

// Rules:
// - Use only the provided data.
// - Do not invent missing values.
// - Comment on performance, workload balance, and trends.
// - Mention departments with highest findings.
// - Keep the response under 180 words.
// - Use a formal and objective tone.
// - Comment on overall audit performance for the stated period.

// JSON Data:
// " . json_encode($analytics, JSON_PRETTY_PRINT);

//       $response = Prism::text()
//     ->using(Provider::Gemini, 'gemini-2.5-flash-lite')
//     ->withPrompt($prompt)
//     ->generate();

// return $response->text;

//     } catch (\Exception $e) {
//         return "Error: " . $e->getMessage();
//     }
// });

require __DIR__.'/settings.php';
