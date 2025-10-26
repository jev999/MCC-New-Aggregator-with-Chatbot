<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\GeminiChatbotController;



Route::post('/chatbot', [ChatbotController::class, 'chat']);

// Gemini Chatbot Routes
Route::post('/gemini-chatbot', [GeminiChatbotController::class, 'chat'])->name('api.gemini.chatbot');
Route::get('/gemini-test', [GeminiChatbotController::class, 'testConnection']);
Route::get('/gemini-faq', [GeminiChatbotController::class, 'getFaqContent']);
Route::post('/test-faq-responses', [GeminiChatbotController::class, 'testFaqResponses']);
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Session heartbeat endpoint
Route::middleware(['web', 'auth'])->post('/heartbeat', function (Request $request) {
    // Update session activity
    $request->session()->put('last_user_activity', now()->timestamp);
    
    return response()->json([
        'status' => 'success',
        'timestamp' => now()->toISOString(),
        'session_id' => $request->session()->getId()
    ]);
});

