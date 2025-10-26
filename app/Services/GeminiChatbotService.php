<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiChatbotService
{
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent';

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key', env('GEMINI_API_KEY'));
    }

    /**
     * Generate a response using Gemini API with FAQ context and conversation history
     */
    public function generateResponse(string $userMessage, string $faqContext, array $conversationHistory = []): string
    {
        try {
            // Check if API key is configured
            if (!$this->apiKey) {
                Log::error('Gemini API key not configured');
                return $this->getFallbackResponse($userMessage);
            }

            // System prompt for MCC context
            $systemInstruction = $this->getSystemPrompt();
            
            // Build conversation context
            $conversationContext = $this->buildConversationContext($conversationHistory);
            
            // Full prompt construction (RAG approach with conversation history)
            $fullPrompt = "{$systemInstruction}\n\n"
                         . "--- START OF FAQ CONTENT ---\n"
                         . "{$faqContext}\n"
                         . "--- END OF FAQ CONTENT ---\n\n"
                         . "{$conversationContext}"
                         . "Current User Question: {$userMessage}";

            // Make API call to Gemini
            $response = $this->callGeminiAPI($fullPrompt);

            if ($response['success']) {
                return $this->cleanResponse($response['message']);
            } else {
                Log::warning('Gemini API call failed, using fallback response');
                return $this->getFallbackResponse($userMessage);
            }

        } catch (\Exception $e) {
            Log::error('Gemini Chatbot Error: ' . $e->getMessage(), [
                'user_message' => $userMessage,
                'conversation_history_count' => count($conversationHistory),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->getFallbackResponse($userMessage);
        }
    }

    /**
     * Call Gemini API with retry logic
     */
    private function callGeminiAPI(string $prompt, int $retries = 2): array
    {
        for ($i = 0; $i <= $retries; $i++) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        $botResponse = trim($data['candidates'][0]['content']['parts'][0]['text']);
                        
                        return [
                            'success' => true,
                            'message' => $botResponse
                        ];
                    }
                }

                // Log the error for debugging
                Log::warning('Gemini API Response Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'attempt' => $i + 1
                ]);

                // If it's the last retry, break
                if ($i === $retries) {
                    break;
                }

                // Wait before retrying
                sleep(1);

            } catch (\Exception $e) {
                Log::error('Gemini API Call Exception', [
                    'message' => $e->getMessage(),
                    'attempt' => $i + 1
                ]);

                if ($i === $retries) {
                    break;
                }
                sleep(1);
            }
        }

        return ['success' => false];
    }

    /**
     * Clean and format the response
     */
    private function cleanResponse(string $response): string
    {
        // Remove any unwanted formatting or characters
        $response = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $response);
        $response = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $response);
        $response = str_replace(["\r\n", "\r"], "\n", $response);
        
        // Ensure proper line breaks for HTML
        $response = nl2br($response);
        
        return $response;
    }

    /**
     * Get FAQ context from file
     */
    public function getFaqContext(): string
    {
        try {
            // Try to read from the resources directory first
            $faqPath = resource_path('data/mcc_faqs.txt');
            
            if (file_exists($faqPath)) {
                $content = file_get_contents($faqPath);
                if ($content !== false) {
                    return $content;
                }
            }
            
            // Fallback to storage directory
            $storageFaqPath = storage_path('app/faqs.txt');
            if (file_exists($storageFaqPath)) {
                $content = file_get_contents($storageFaqPath);
                if ($content !== false) {
                    return $content;
                }
            }
            
            // Fallback to default if file not found or can't be read
            Log::warning('FAQ file not found or unreadable, using default context', [
                'primary_path' => $faqPath,
                'secondary_path' => $storageFaqPath,
                'primary_exists' => file_exists($faqPath),
                'secondary_exists' => file_exists($storageFaqPath)
            ]);
            
            return $this->getDefaultFaqContext();
        } catch (\Exception $e) {
            Log::error('Failed to read FAQ file: ' . $e->getMessage());
            return $this->getDefaultFaqContext();
        }
    }

    /**
     * Get default FAQ context if file is not available
     */
    private function getDefaultFaqContext(): string
    {
        return "Q: What is MCC?\nA: Madridejos Community College (MCC) is located in Bunakan, Madridejos, Cebu, Philippines.\n\nQ: What programs does MCC offer?\nA: MCC offers BSIT, BSBA, BEED, BSED, and BSHM programs.\n\nQ: How can I contact MCC?\nA: Email info@mcc-nac.edu.ph or call (032) 123-4567.";
    }

    /**
     * Build conversation context from history
     */
    private function buildConversationContext(array $conversationHistory): string
    {
        if (empty($conversationHistory)) {
            return '';
        }

        $context = "--- CONVERSATION HISTORY ---\n";
        
        foreach ($conversationHistory as $message) {
            $role = $message['role'] === 'user' ? 'User' : 'Assistant';
            $content = $message['content'];
            $context .= "{$role}: {$content}\n";
        }
        
        $context .= "--- END CONVERSATION HISTORY ---\n\n";
        
        return $context;
    }

    /**
     * Get system prompt for MCC context
     */
    private function getSystemPrompt(): string
    {
        return "You are MCC-NAC Assistant, an AI chatbot for **Madridejos Community College (MCC-NAC Portal System)**. Provide accurate, friendly, and professional responses about MCC based on the comprehensive FAQ database provided.

### **RESPONSE GUIDELINES**
- Answer questions based ONLY on the provided FAQ content and conversation history
- Be concise but helpful (2-4 sentences for simple queries, more detail for complex topics)
- Use **bold** for important details (e.g., contact information, deadlines, program names)
- Maintain a **friendly but professional** tone appropriate for a college environment
- Reference previous conversation context when relevant
- If the answer is not in the provided FAQ content, politely state that you don't have that specific information and direct users to contact MCC directly

### **CORE MCC INFORMATION**
- **Full Name:** Madridejos Community College (MCC)
- **Location:** Bunakan, Madridejos, Cebu, Philippines
- **Main Email:** info@mcc-nac.edu.ph
- **Phone:** (032) 394-2234
- **Office Hours:** 8:00 AM - 5:00 PM (Monday to Friday)

### **ACADEMIC PROGRAMS**
- **BSIT** - Bachelor of Science in Information Technology
- **BSBA** - Bachelor of Science in Business Administration  
- **BEED** - Bachelor of Elementary Education
- **BSED** - Bachelor of Secondary Education
- **BSHM** - Bachelor of Science in Hospitality Management

### **KEY CONTACTS**
- **Registrar:** registrar@mcc-nac.edu.ph
- **Admissions:** admissions@mcc-nac.edu.ph
- **General Info:** info@mcc-nac.edu.ph

### **IMPORTANT NOTES**
- MCC is a PUBLIC community college offering tuition-free education
- Only registration fees apply (₱100 for first-year SSC fee)
- All programs are CHED-accredited
- Always prioritize accuracy and direct users to official channels for specific inquiries";
    }

    /**
     * Fallback response when API is unavailable
     */
    private function getFallbackResponse(string $userMessage): string
    {
        // Simple keyword-based fallback responses with accurate information
        $message = strtolower($userMessage);
        
        if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false || strpos($message, 'hey') !== false) {
            return "Hello! Welcome to MCC-NAC Portal! 👋 I'm your MCC-NAC Assistant. I can help you with information about our academic programs, admissions, campus facilities, and more. What would you like to know about Madridejos Community College?";
        }
        
        if (strpos($message, 'program') !== false || strpos($message, 'course') !== false || strpos($message, 'degree') !== false) {
            return "🎓 <strong>MCC Academic Programs (CHED-Accredited):</strong><br><br>
            • <strong>BSIT</strong> - Bachelor of Science in Information Technology<br>
            • <strong>BSBA</strong> - Bachelor of Science in Business Administration<br>
            • <strong>BEED</strong> - Bachelor of Elementary Education<br>
            • <strong>BSED</strong> - Bachelor of Secondary Education<br>
            • <strong>BSHM</strong> - Bachelor of Science in Hospitality Management<br><br>
            All programs are tuition-free as MCC is a public community college. For detailed curriculum information, contact <strong>registrar@mcc-nac.edu.ph</strong> or call <strong>(032) 394-2234</strong>.";
        }
        
        if (strpos($message, 'admission') !== false || strpos($message, 'enroll') !== false || strpos($message, 'apply') !== false) {
            return "📚 <strong>MCC Admission Requirements:</strong><br><br>
            1. Completed application form<br>
            2. Form 138 (Report Card/Transcript)<br>
            3. PSA Birth Certificate (original & photocopy)<br>
            4. 2x2 ID pictures (recent)<br>
            5. Good Moral Certificate<br>
            6. Registration fee: ₱100 (SSC fee for first-year students)<br><br>
            For enrollment assistance, contact <strong>admissions@mcc-nac.edu.ph</strong> or visit our campus at Bunakan, Madridejos, Cebu.";
        }
        
        if (strpos($message, 'location') !== false || strpos($message, 'address') !== false || strpos($message, 'where') !== false) {
            return "📍 <strong>Madridejos Community College Location:</strong><br><br>
            <strong>Bunakan, Madridejos, Cebu, Philippines</strong><br>
            Near Madridejos Public Market and Municipal Hall<br><br>
            <strong>Office Hours:</strong> 8:00 AM - 5:00 PM (Monday to Friday)<br>
            <strong>Phone:</strong> (032) 394-2234";
        }
        
        if (strpos($message, 'contact') !== false || strpos($message, 'phone') !== false || strpos($message, 'email') !== false) {
            return "📞 <strong>MCC Contact Information:</strong><br><br>
            • <strong>Main Email:</strong> info@mcc-nac.edu.ph<br>
            • <strong>Registrar:</strong> registrar@mcc-nac.edu.ph<br>
            • <strong>Admissions:</strong> admissions@mcc-nac.edu.ph<br>
            • <strong>Phone:</strong> (032) 394-2234<br>
            • <strong>Address:</strong> Bunakan, Madridejos, Cebu<br>
            • <strong>Hours:</strong> 8:00 AM - 5:00 PM (Mon-Fri)";
        }
        
        if (strpos($message, 'fee') !== false || strpos($message, 'tuition') !== false || strpos($message, 'cost') !== false) {
            return "💰 <strong>MCC Tuition and Fees:</strong><br><br>
            <strong>Good news!</strong> MCC offers <strong>tuition-free education</strong> as a public community college.<br><br>
            <strong>Only fee required:</strong><br>
            • Registration fee for first-year students: <strong>₱100</strong> (SSC fee)<br><br>
            For detailed fee information, contact <strong>registrar@mcc-nac.edu.ph</strong> or call <strong>(032) 394-2234</strong>.";
        }
        
        if (strpos($message, 'bsit') !== false || strpos($message, 'faculty') !== false || strpos($message, 'instructor') !== false) {
            return "👨‍🏫 <strong>BSIT Department Faculty:</strong><br><br>
            • <strong>Mr. Dino Ilustrisimo</strong> - Department Head<br>
            • <strong>Mr. Alvin Billones</strong> - Instructor<br>
            • <strong>Mr. Juniel Marfa</strong> - Instructor<br>
            • <strong>Mr. Danilo Villarino</strong> - Instructor<br>
            • <strong>Mr. Richard Bracero</strong> - Instructor<br>
            • <strong>Mr. Jered Cueva</strong> - Instructor<br>
            • <strong>Mrs. Jessica Alcazar</strong> - Instructor<br>
            • <strong>Mrs. Emily Ilustrisimo</strong> - Instructor<br><br>
            For more information about BSIT program, contact <strong>info@mcc-nac.edu.ph</strong>.";
        }
        
        if (strpos($message, 'thank') !== false) {
            return "You're very welcome! 😊 I'm here to help with any questions about MCC. Feel free to ask more or contact us directly at <strong>info@mcc-nac.edu.ph</strong> or <strong>(032) 394-2234</strong>. Have a great day!";
        }
        
        // Default response
        return "Thank you for your question about MCC! 😊 I'm currently experiencing technical difficulties with my AI service, but you can get immediate assistance by contacting:<br><br>
        📧 <strong>info@mcc-nac.edu.ph</strong><br>
        📞 <strong>(032) 394-2234</strong><br>
        📍 <strong>Bunakan, Madridejos, Cebu</strong><br><br>
        How else can I help you with information about Madridejos Community College?";
    }
}
