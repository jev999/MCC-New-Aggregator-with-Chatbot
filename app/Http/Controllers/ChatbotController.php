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
        // Simple keyword-based fallback responses
        $message = strtolower($userMessage);
        
        if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false || strpos($message, 'hey') !== false) {
            return "Hello! Welcome to MCC Portal! 👋 I'm here to help you with information about our programs, events, admissions, and more. What would you like to know?";
        }
        
        if (strpos($message, 'program') !== false || strpos($message, 'course') !== false || strpos($message, 'degree') !== false) {
            return "🎓 <strong>MCC Academic Programs:</strong><br><br>
            • <strong>BSIT</strong> - Bachelor of Science in Information Technology<br>
            • <strong>BSBA</strong> - Bachelor of Science in Business Administration<br>
            • <strong>BEED</strong> - Bachelor of Elementary Education<br>
            • <strong>BSED</strong> - Bachelor of Secondary Education<br>
            • <strong>BSHM</strong> - Bachelor of Science in Hospitality Management<br><br>
            Each program is CHED-accredited and designed for industry readiness. For detailed curriculum, visit MCC or email <strong>registrar@mcc-nac.edu.ph</strong>.";
        }
        
        if (strpos($message, 'admission') !== false || strpos($message, 'enroll') !== false || strpos($message, 'apply') !== false) {
            return "📚 <strong>Admission Process:</strong><br><br>
            1. Submit completed application form<br>
            2. Provide Form 138 (Report Card)<br>
            3. Submit 2x2 ID pictures<br>
            4. Present PSA Birth Certificate<br>
            5. Pay registration fee<br><br>
            For inquiries, contact <strong>admissions@mcc-nac.edu.ph</strong> or visit MCC Campus at Bunakan, Madridejos, Cebu.";
        }
        
        if (strpos($message, 'location') !== false || strpos($message, 'address') !== false || strpos($message, 'where') !== false) {
            return "📍 <strong>Madridejos Community College located:</strong><br><br>
            <strong>Bunakan, Madridejos, Cebu, Philippines</strong><br>
            Near Madridejos Public Market and Municipal Hall.<br><br>
            Landmark:Brgy. Bunakan, Madridejos,cebu .<br><br>
            Open from <strong>8:00 AM to 5:00 PM</strong> (Monday to Friday).";
        }
        
        if (strpos($message, 'contact') !== false || strpos($message, 'phone') !== false || strpos($message, 'email') !== false) {
            return "📞 <strong>Contact MCC:</strong><br><br>
            • <strong>Email:</strong> info@mcc-nac.edu.ph<br>
            • <strong>Registrar:</strong> registrar@mcc-nac.edu.ph<br>
            • <strong>Admissions:</strong> admissions@mcc-nac.edu.ph<br>
            • <strong>Landline:</strong> (032) 123-4567<br><br>
            Visit us at <strong>Bunakan, Madridejos, Cebu</strong>.";
        }
        
        if (strpos($message, 'event') !== false || strpos($message, 'activity') !== false || strpos($message, 'announcement') !== false) {
            return "📅 <strong>MCC Events & Activities:</strong><br><br>
            • <strong>Foundation Month</strong> (Annual celebration every March)<br>
            • <strong>Intramurals</strong> (Sports competitions among departments)<br>
            • <strong>IT Days</strong> (BSIT department and other departments)<br>
            • <strong>Teacher’s Day</strong> (Honoring educators)<br><br>
            Check the <strong>MCC-NAC Portal</strong> for updates or email <strong>events@mcc-nac.edu.ph</strong>.";
        }
        
        if (strpos($message, 'fee') !== false || strpos($message, 'tuition') !== false || strpos($message, 'cost') !== false || strpos($message, 'price') !== false) {
            return "💰 <strong>Offers no tuition fee :</strong><br><br>
            • <strong>The registration fee is exclusive only for incoming first year cost (100 peso) for SSC for courses BSIT/BSBA/BSHM/BEED/BSED:</strong>
            <em>Note: for more information visit us <strong>infog@mcc-nac.edu.ph</strong>.</em>";
        }
        
        if (strpos($message, 'facility') !== false || strpos($message, 'library') !== false || strpos($message, 'lab') !== false) {
            return "🏫 <strong>MCC Campus Facilities:</strong><br><br>
            • <strong>Library</strong> (Open 8AM-5PM)<br>
            • <strong>Computer Labs</strong> (For BSIT students)<br>
            • <strong>Educ</strong> (For education students)<br>
            • <strong>Gymnasium</strong> (Sports & events)<br>
            • <strong>Cafeteria</strong> (Affordable meals)<br><br>
            Visit MCC to explore our facilities!";
        }
        
        if (strpos($message, 'scholarship') !== false || strpos($message, 'discount') !== false) {
            return "🎗️ <strong>Scholarships & Financial Aid:</strong><br><br>
            MCC offers:<br>
            • <strong>Academic Scholarships</strong> (For TED graant)<br>
            • <strong>CHED Grants</strong> (For qualified applicants)<br>
            • <strong>LGU Scholarships</strong> (Madridejos residents)<br><br>
            Inquire at the <strong>Registrars Office</strong> or email <strong>scholarships@mcc-nac.edu.ph</strong>.";
        }
        
        if (strpos($message, 'calendar') !== false || strpos($message, 'semester') !== false) {
            return "📆 <strong>Academic Calendar 2024-2025:</strong><br><br>
            • <strong>1st Semester:</strong> June - October<br>
            • <strong>2nd Semester:</strong> November - March<br>
            • <strong>Summer Term:</strong> April - May (optional)<br><br>
            Exact dates vary yearly. Check the <strong>MCC-NAC Portal</strong> for updates.";
        }
        if (strpos($message, 'bsit') !== false || strpos($message, 'bsit instructor') !== false) {
            return " <strong>Bachelor of Science Information Technology:</strong><br><br>
            • <strong>BSIT Head:</strong>Mr.Dino ilustrisimo<br>
            • <strong>Instructor:</strong> Mr.Alvin Billones <br>
            • <strong>Instructor:</strong>Mr. Juniel Marfa<br><br>
            • <strong>Instructor:</strong>Mr. Danilo Villarino<br><br>
            • <strong>Instructor:</strong>Mr. Richard Bracero<br><br>
            • <strong>Instructor:</strong>Mr. Jered Cueva<br><br>
            • <strong>Instructor:</strong>Mrs. Jessica Alcazar<br><br>
            • <strong>Instructor:</strong>Mrs. Emily Ilustrisimo<br><br>
            Exact dates vary yearly. Check the <strong>MCC-NAC Portal</strong> for updates.";
        }
        
        if (strpos($message, 'thank') !== false) {
            return "You're very welcome! 😊 If you have more questions, feel free to ask or email <strong>info@mcc-nac.edu.ph</strong>. Have a great day at MCC!";
        }
        
        // Default response
        return "Thank you for your question! 😊 For detailed information, please contact:<br><br>
        📧 <strong>info@mcc-nac.edu.ph</strong><br>
        📍 <strong>Bunakan, Madridejos, Cebu</strong><br><br>
        How else can I assist you with MCC?";
    }

    private function getSystemPrompt(): string
    {
        return "You are an AI assistant for **Madridejos Community College (MCC-NAC Portal System)**. Provide accurate, friendly, and professional responses about MCC. Key details:

### **COLLEGE INFORMATION**
- **Full Name:** Madridejos Community College (MCC)
- **Location:** Bunakan, Madridejos, Cebu, Philippines
- **Email:** info@mcc-nac.edu.ph
- **Contact:** (032) 123-4567
- **Operating Hours:** 8:00 AM - 5:00 PM (Mon-Fri)

### **ACADEMIC PROGRAMS**
1. **BSIT** (Bachelor of Science in Information Technology)  
   - Focus: Programming, Networking, AI, and Software Development  
   - Career Paths: Software Engineer, IT Specialist, Data Analyst  

2. **BSBA** (Bachelor of Science in Business Administration)  
   - Specializations: Marketing, Finance, Entrepreneurship  
   - Career Paths: Business Manager, Financial Analyst  

3. **BEED/BSED** (Bachelor of Elementary/Secondary Education)  
   - Specializations: English, Math, Science  
   - Career Paths: Teacher, Education Consultant  

4. **BSHM** (Bachelor of Science in Hospitality Management)  
   - Focus: Hotel Operations, Tourism, Event Management  

### **ADMISSION REQUIREMENTS**
- Form 138 (Report Card)  
- PSA Birth Certificate  
- 2x2 ID Pictures  
- Good Moral Certificate  

### **CAMPUS FACILITIES**
- Library (Open 8AM-5PM)  
- Computer Labs (24/7 Access for BSIT)  
- Gymnasium (Sports & Events)  
- Cafeteria (Affordable Meals)  

### **STUDENT SERVICES**
- **Registrar’s Office:** Handles enrollment, grades  
- **Guidance Office:** Counseling & career advice  
- **Scholarships:** Academic, CHED, LGU-funded  

### **PORTAL FEATURES**
- **Announcements:** Exam schedules, events  
- **Grades Viewing:** Check academic performance  
- **Event Registration:** Sign up for seminars  

### **RESPONSE GUIDELINES**
- Be concise but helpful (2-3 sentences for simple queries).  
- For fees, deadlines, or specific policies, direct users to official contacts.  
- Use **bold** for important details (e.g., deadlines, emails).  
- Maintain a **friendly but professional** tone.  

Example Responses:  
- *“MCC’s BSIT program covers programming, databases, and AI. Email admissions@mcc-nac.edu.ph for the full curriculum.”*  
- *“The library is open weekdays from 8AM-5PM. Bring your student ID for access.”*  
- *“For scholarship applications, visit the Registrar’s Office or email scholarships@mcc-nac.edu.ph.”*";
    }
}