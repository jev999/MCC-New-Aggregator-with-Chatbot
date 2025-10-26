<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{
    private $deepseekApiKey;
    private $deepseekApiUrl = 'https://api.deepseek.com/v1/chat/completions';

    public function __construct()
    {
        $this->deepseekApiKey = config('app.deepseek_api_key', env('DEEPSEEK_API_KEY'));
    }

    public function chat(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            $userMessage = $request->input('message');
            
            // Check if API key is configured
            if (!$this->deepseekApiKey) {
                Log::error('DeepSeek API key not configured');
                return response()->json([
                    'success' => false,
                    'response' => $this->getFallbackResponse($userMessage),
                    'debug' => [
                        'api_key_configured' => false,
                        'env_deepseek_key' => env('DEEPSEEK_API_KEY') ? 'exists' : 'missing'
                    ]
                ]);
            }

            // Get conversation context
            $conversationId = $request->input('conversation_id', 'default');
            
            // System prompt for MCC context
            $systemPrompt = $this->getSystemPrompt();
            
            // Prepare messages for DeepSeek API
            $messages = [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage
                ]
            ];

            // Make API call to DeepSeek with retry logic
            $response = $this->callDeepSeekAPI($messages);

            if ($response['success']) {
                return response()->json([
                    'success' => true,
                    'response' => $response['message'],
                    'conversation_id' => $conversationId
                ]);
            } else {
                return response()->json([
                    'success' => true, // Still return success but with fallback
                    'response' => $this->getFallbackResponse($userMessage)
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'response' => 'Please provide a valid message (maximum 1000 characters).'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage(), [
                'user_message' => $userMessage ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => true, // Return success with fallback response
                'response' => $this->getFallbackResponse($userMessage ?? '')
            ]);
        }
    }

    private function callDeepSeekAPI(array $messages, int $retries = 2): array
    {
        for ($i = 0; $i <= $retries; $i++) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->deepseekApiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post($this->deepseekApiUrl, [
                    'model' => 'deepseek-chat',
                    'messages' => $messages,
                    'max_tokens' => 800,
                    'temperature' => 0.7,
                    'top_p' => 0.9,
                    'stream' => false
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['choices'][0]['message']['content'])) {
                        $botResponse = trim($data['choices'][0]['message']['content']);
                        
                        // Clean up the response
                        $botResponse = $this->cleanResponse($botResponse);
                        
                        return [
                            'success' => true,
                            'message' => $botResponse
                        ];
                    }
                }

                // Log the error for debugging
                Log::warning('DeepSeek API Response Error', [
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
                Log::error('DeepSeek API Call Exception', [
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
        
        if (strpos($message, 'event') !== false || strpos($message, 'activity') !== false || strpos($message, 'announcement') !== false) {
            return "📅 <strong>MCC Events & Activities:</strong><br><br>
            • <strong>Foundation Day</strong> - Annual college celebration<br>
            • <strong>Intramurals</strong> - Inter-department sports competitions<br>
            • <strong>IT Days</strong> - Technology showcase for BSIT students<br>
            • <strong>Teachers' Day</strong> - Honoring educators<br>
            • <strong>Graduation Ceremonies</strong> - Commencement exercises<br><br>
            Check the <strong>MCC-NAC Portal</strong> for current events or contact <strong>info@mcc-nac.edu.ph</strong>.";
        }
        
        if (strpos($message, 'fee') !== false || strpos($message, 'tuition') !== false || strpos($message, 'cost') !== false || strpos($message, 'price') !== false) {
            return "💰 <strong>MCC Tuition and Fees:</strong><br><br>
            <strong>Good news!</strong> MCC offers <strong>tuition-free education</strong> as a public community college.<br><br>
            <strong>Only fee required:</strong><br>
            • Registration fee for first-year students: <strong>₱100</strong> (SSC fee)<br><br>
            For detailed fee information, contact <strong>registrar@mcc-nac.edu.ph</strong> or call <strong>(032) 394-2234</strong>.";
        }
        
        if (strpos($message, 'facility') !== false || strpos($message, 'library') !== false || strpos($message, 'lab') !== false) {
            return "🏫 <strong>MCC Campus Facilities:</strong><br><br>
            • <strong>Library</strong> - Study areas and research resources (8AM-5PM)<br>
            • <strong>Computer Laboratories</strong> - Modern equipment for all programs<br>
            • <strong>Science Laboratories</strong> - For education and science courses<br>
            • <strong>Gymnasium</strong> - Sports activities and events<br>
            • <strong>Cafeteria</strong> - Affordable meals and snacks<br>
            • <strong>Audio-Visual Rooms</strong> - Multimedia learning spaces<br><br>
            Visit MCC to explore our modern facilities!";
        }
        
        if (strpos($message, 'scholarship') !== false || strpos($message, 'discount') !== false) {
            return "🎗️ <strong>MCC Scholarships & Financial Aid:</strong><br><br>
            Available scholarships:<br>
            • <strong>CHED Grants</strong> - Government financial assistance<br>
            • <strong>TES (Tertiary Education Subsidy)</strong> - For qualified students<br>
            • <strong>LGU Scholarships</strong> - For Madridejos residents<br>
            • <strong>Academic Scholarships</strong> - Merit-based awards<br>
            • <strong>IP Scholarships</strong> - For Indigenous Peoples<br><br>
            Contact the <strong>Registrar's Office</strong> at <strong>registrar@mcc-nac.edu.ph</strong> for application procedures.";
        }
        
        if (strpos($message, 'calendar') !== false || strpos($message, 'semester') !== false) {
            return "📆 <strong>MCC Academic Calendar:</strong><br><br>
            • <strong>First Semester:</strong> August - December<br>
            • <strong>Second Semester:</strong> January - May<br>
            • <strong>Summer Term:</strong> June - July (optional)<br><br>
            Enrollment periods are announced before each semester. Check the <strong>MCC-NAC Portal</strong> or contact <strong>registrar@mcc-nac.edu.ph</strong> for exact dates.";
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
            For more information about the BSIT program, contact <strong>info@mcc-nac.edu.ph</strong> or call <strong>(032) 394-2234</strong>.";
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

    private function getSystemPrompt(): string
    {
        return "You are MCC-NAC Assistant, an AI chatbot for **Madridejos Community College (MCC-NAC Portal System)**. Provide accurate, friendly, and professional responses about MCC based on verified information.

### **CORE MCC INFORMATION**
- **Full Name:** Madridejos Community College (MCC)
- **Location:** Bunakan, Madridejos, Cebu, Philippines
- **Main Email:** info@mcc-nac.edu.ph
- **Phone:** (032) 394-2234
- **Office Hours:** 8:00 AM - 5:00 PM (Monday to Friday)

### **ACADEMIC PROGRAMS (CHED-Accredited)**
1. **BSIT** - Bachelor of Science in Information Technology
   - Focus: Programming, Networking, AI, Software Development
   - Faculty Head: Mr. Dino Ilustrisimo

2. **BSBA** - Bachelor of Science in Business Administration
   - Specializations: Marketing, Finance, Entrepreneurship

3. **BEED** - Bachelor of Elementary Education
   - Prepares future elementary school teachers

4. **BSED** - Bachelor of Secondary Education
   - Specializations: English, Math, Science

5. **BSHM** - Bachelor of Science in Hospitality Management
   - Focus: Hotel Operations, tourism, Event Management

### **TUITION & FEES**
- **MCC is a PUBLIC community college offering TUITION-FREE education**
- Only registration fee: ₱100 (SSC fee for first-year students)
- All programs are CHED-accredited

### **ADMISSION REQUIREMENTS**
- Completed application form
- Form 138 (Report Card/Transcript)
- PSA Birth Certificate (original & photocopy)
- 2x2 ID pictures (recent)
- Good Moral Certificate
- Registration fee payment

### **KEY CONTACTS**
- **Registrar:** registrar@mcc-nac.edu.ph
- **Admissions:** admissions@mcc-nac.edu.ph
- **General Info:** info@mcc-nac.edu.ph

### **CAMPUS FACILITIES**
- Library (8AM-5PM with study areas)
- Computer Laboratories (modern equipment)
- Science Laboratories
- Gymnasium (sports & events)
- Cafeteria (affordable meals)
- Audio-Visual Rooms

### **RESPONSE GUIDELINES**
- Be concise but helpful (2-4 sentences for simple queries)
- Use **bold** for important details (contact info, deadlines, program names)
- Maintain a **friendly but professional** tone
- For specific policies or detailed information, direct users to official contacts
- Always emphasize that MCC offers tuition-free education as a public college
- Provide accurate contact information: (032) 394-2234 and info@mcc-nac.edu.ph";
    }
}