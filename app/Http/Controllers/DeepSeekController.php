<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekController extends Controller
{
    public function handleChatRequest(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'messages' => 'required|array|min:1',
            'messages.*.role' => 'required|in:system,user,assistant',
            'messages.*.content' => 'required|string',
            'temperature' => 'sometimes|numeric|between:0,2',
            'max_tokens' => 'sometimes|integer|min:1|max:4096',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.config('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'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)
              ->post('https://api.deepseek.com/v1/chat/completions', $validated);

            return $response->json();

        } catch (\Exception $e) {
            Log::error('DeepSeek API Error: '.$e->getMessage());
            return response()->json([
                'error' => 'Failed to process request',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}