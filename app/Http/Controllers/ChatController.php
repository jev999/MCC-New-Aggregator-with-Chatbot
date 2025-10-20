<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function showChat()
    {
        return view('chat');
    }

    public function handleChat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string'
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('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'),
            'Content-Type' => 'application/json',
        ])->post('https://api.deepseek.com/v1/chat/completions', [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'user', 'content' => $validated['message']]
            ]
        ]);

        return back()->with('response', $response->json('choices.0.message.content'));
    }
}
