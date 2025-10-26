<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\GeminiChatbotService;
use Illuminate\Support\Facades\Log;

class GeminiChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(GeminiChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Handle chat requests using Gemini API
     */
    public function chat(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $request->validate([
                'message' => 'required|string|max:1000',
                'conversation_history' => 'sometimes|array|max:20'
            ]);

            $userMessage = $request->input('message');
            $conversationHistory = $request->input('conversation_history', []);
            
            // Get FAQ context
            $faqContext = $this->chatbotService->getFaqContext();
            
            // Generate response using Gemini API with conversation context
            $aiResponse = $this->chatbotService->generateResponse($userMessage, $faqContext, $conversationHistory);

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
                'conversation_id' => $request->input('conversation_id', 'default'),
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'response' => 'Please provide a valid message (maximum 1000 characters).'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Gemini Chatbot Controller Error: ' . $e->getMessage(), [
                'user_message' => $userMessage ?? 'N/A',
                'conversation_history_count' => count($conversationHistory ?? []),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'response' => 'Sorry, there was an issue processing your request. Please try again later.'
            ], 500);
        }
    }

    /**
     * Test endpoint to verify Gemini API connection
     */
    public function testConnection(): JsonResponse
    {
        try {
            $apiKey = config('gemini.api_key', env('GEMINI_API_KEY'));
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini API key not configured',
                    'instructions' => [
                        '1. Add GEMINI_API_KEY to your .env file',
                        '2. Get your API key from https://aistudio.google.com/app/apikey',
                        '3. Run: php artisan config:clear'
                    ]
                ]);
            }

            // Test with a simple message
            $testMessage = "Hello, can you respond with 'Gemini API connection successful'?";
            $faqContext = $this->chatbotService->getFaqContext();
            
            $response = $this->chatbotService->generateResponse($testMessage, $faqContext);
            
            return response()->json([
                'success' => true,
                'message' => 'Gemini API connection successful',
                'test_response' => $response,
                'api_key_configured' => true,
                'faq_context_loaded' => !empty($faqContext)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API connection failed',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get FAQ content for debugging
     */
    public function getFaqContent(): JsonResponse
    {
        try {
            $faqContent = $this->chatbotService->getFaqContext();
            $faqPath = storage_path('app/faqs.txt');
            $fileExists = file_exists($faqPath);
            
            return response()->json([
                'success' => true,
                'faq_content' => $faqContent,
                'content_length' => strlen($faqContent),
                'file_exists' => $fileExists,
                'file_path' => $faqPath,
                'is_default_content' => !$fileExists || strlen($faqContent) < 1000
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test FAQ-based responses without Gemini API
     */
    public function testFaqResponses(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            $userMessage = strtolower($request->input('message'));
            $faqContent = $this->chatbotService->getFaqContext();
            
            // Simple keyword-based FAQ matching
            $response = $this->getFaqBasedResponse($userMessage, $faqContent);

            return response()->json([
                'success' => true,
                'response' => $response,
                'faq_content_used' => true,
                'message_analyzed' => $userMessage
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Simple FAQ-based response matching
     */
    private function getFaqBasedResponse(string $message, string $faqContent): string
    {
        // Convert FAQ content to array of Q&A pairs
        $lines = explode("\n", $faqContent);
        $qaPairs = [];
        $currentQ = '';
        $currentA = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, 'Q:') === 0) {
                if ($currentQ && $currentA) {
                    $qaPairs[] = ['question' => $currentQ, 'answer' => $currentA];
                }
                $currentQ = $line;
                $currentA = '';
            } elseif (strpos($line, 'A:') === 0) {
                $currentA = $line;
            } elseif ($currentA && $line) {
                $currentA .= ' ' . $line;
            }
        }
        
        if ($currentQ && $currentA) {
            $qaPairs[] = ['question' => $currentQ, 'answer' => $currentA];
        }
        
        // Find best matching Q&A with priority scoring
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($qaPairs as $qa) {
            $question = strtolower($qa['question']);
            $answer = $qa['answer'];
            $score = 0;
            
            // Priority 1: Exact faculty/instructor matches
            if ((strpos($message, 'faculty') !== false || strpos($message, 'instructor') !== false || strpos($message, 'teacher') !== false) && 
                (strpos($question, 'instructor') !== false || strpos($question, 'faculty') !== false)) {
                $score = 100;
            }
            // Priority 2: BSIT + faculty combination
            elseif (strpos($message, 'bsit') !== false && (strpos($message, 'faculty') !== false || strpos($message, 'instructor') !== false) && 
                    strpos($question, 'bsit') !== false && (strpos($question, 'instructor') !== false || strpos($question, 'faculty') !== false)) {
                $score = 90;
            }
            // Priority 3: Specific topic matches
            elseif (strpos($message, 'admission') !== false && strpos($question, 'admission') !== false) {
                $score = 80;
            }
            elseif (strpos($message, 'fee') !== false && strpos($question, 'fee') !== false) {
                $score = 80;
            }
            elseif (strpos($message, 'contact') !== false && strpos($question, 'contact') !== false) {
                $score = 80;
            }
            elseif (strpos($message, 'scholarship') !== false && strpos($question, 'scholarship') !== false) {
                $score = 80;
            }
            elseif (strpos($message, 'event') !== false && strpos($question, 'event') !== false) {
                $score = 80;
            }
            // Priority 4: General program matches
            elseif (strpos($message, 'program') !== false && strpos($question, 'program') !== false) {
                $score = 70;
            }
            elseif (strpos($message, 'bsit') !== false && strpos($question, 'bsit') !== false) {
                $score = 70;
            }
            elseif (strpos($message, 'location') !== false && strpos($question, 'location') !== false) {
                $score = 70;
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $qa;
            }
        }
        
        if ($bestMatch && $bestScore > 0) {
            return $this->formatAnswer($bestMatch['answer']);
        }
        
        return "I found information about MCC in our FAQ database, but I need more specific details. Could you please rephrase your question or ask about specific topics like programs, admissions, contact information, or fees?";
    }

    /**
     * Format the answer for better display
     */
    private function formatAnswer(string $answer): string
    {
        // Remove "A:" prefix
        $answer = preg_replace('/^A:\s*/', '', $answer);
        
        // Convert to HTML format
        $answer = nl2br($answer);
        
        // Make emails and important info bold
        $answer = preg_replace('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', '<strong>$1</strong>', $answer);
        $answer = preg_replace('/(\(032\)\s*\d{3}-\d{4})/', '<strong>$1</strong>', $answer);
        
        return $answer;
    }
}
