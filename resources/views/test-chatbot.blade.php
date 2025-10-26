<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Chatbot - MCC</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8fafc;
            min-height: 100vh;
        }
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .test-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .test-header h1 {
            color: #1e40af;
            margin-bottom: 0.5rem;
        }
        .test-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .test-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        .test-btn {
            padding: 0.75rem 1.5rem;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .test-btn:hover {
            background: #1e3a8a;
            transform: translateY(-2px);
        }
        .test-btn.secondary {
            background: #6b7280;
        }
        .test-btn.secondary:hover {
            background: #4b5563;
        }
        .status {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .status.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .status.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .status.info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #1e40af;
            text-decoration: none;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .back-link:hover {
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <a href="{{ route('welcome') }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Welcome Page
        </a>
        
        <div class="test-header">
            <h1><i class="fas fa-robot"></i> MCC Chatbot Test</h1>
            <p>Test the enhanced Gemini AI chatbot functionality</p>
        </div>
        
        <div class="test-info">
            <h3><i class="fas fa-info-circle"></i> Test Information</h3>
            <p>This page allows you to test the enhanced chatbot features including:</p>
            <ul>
                <li>Modern UI design with traditional chatbot interface</li>
                <li>Conversation history and context</li>
                <li>Quick action buttons</li>
                <li>Enhanced typing indicators</li>
                <li>Message formatting and animations</li>
            </ul>
        </div>
        
        <div class="test-buttons">
            <button class="test-btn" onclick="testConnection()">
                <i class="fas fa-plug"></i> Test API Connection
            </button>
            <button class="test-btn" onclick="testFaqContent()">
                <i class="fas fa-question-circle"></i> Test FAQ Content
            </button>
            <button class="test-btn" onclick="testFaqResponses()">
                <i class="fas fa-robot"></i> Test FAQ Responses
            </button>
            <button class="test-btn secondary" onclick="openChatbot()">
                <i class="fas fa-comments"></i> Open Chatbot
            </button>
        </div>
        
        <div id="test-results"></div>
    </div>

    @include('components.gemini-chatbot-widget')

    <script>
        function showStatus(message, type = 'info') {
            const resultsDiv = document.getElementById('test-results');
            const statusDiv = document.createElement('div');
            statusDiv.className = `status ${type}`;
            statusDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
            resultsDiv.appendChild(statusDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                statusDiv.remove();
            }, 5000);
        }

        async function testConnection() {
            showStatus('Testing Gemini API connection...', 'info');
            
            try {
                const response = await fetch('/api/gemini-test');
                const data = await response.json();
                
                if (data.success) {
                    showStatus(`✅ Connection successful! API key configured: ${data.api_key_configured ? 'Yes' : 'No'}`, 'success');
                } else {
                    showStatus(`❌ Connection failed: ${data.message}`, 'error');
                }
            } catch (error) {
                showStatus(`❌ Connection error: ${error.message}`, 'error');
            }
        }

        async function testFaqContent() {
            showStatus('Testing FAQ content loading...', 'info');
            
            try {
                const response = await fetch('/api/gemini-faq');
                const data = await response.json();
                
                if (data.success) {
                    showStatus(`✅ FAQ content loaded! Length: ${data.content_length} characters`, 'success');
                } else {
                    showStatus(`❌ FAQ content error: ${data.error}`, 'error');
                }
            } catch (error) {
                showStatus(`❌ FAQ content error: ${error.message}`, 'error');
            }
        }

        async function testFaqResponses() {
            showStatus('Testing FAQ-based responses...', 'info');
            
            const testQuestions = [
                'What programs does MCC offer?',
                'Who are the BSIT instructors?',
                'What are the admission requirements?',
                'How much is the registration fee?',
                'What are the contact details?'
            ];
            
            for (let i = 0; i < testQuestions.length; i++) {
                const question = testQuestions[i];
                showStatus(`Testing: "${question}"`, 'info');
                
                try {
                    const response = await fetch('/api/test-faq-responses', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ message: question })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showStatus(`✅ "${question}" → ${data.response.substring(0, 100)}...`, 'success');
                    } else {
                        showStatus(`❌ "${question}" → Error: ${data.error}`, 'error');
                    }
                } catch (error) {
                    showStatus(`❌ "${question}" → Error: ${error.message}`, 'error');
                }
                
                // Small delay between tests
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            showStatus('FAQ response testing completed!', 'success');
        }

        function openChatbot() {
            const container = document.getElementById('gemini-chatbot-container');
            if (container) {
                container.classList.add('active');
                showStatus('Chatbot opened! Try asking about MCC programs, admissions, or contact information.', 'info');
            }
        }

        // Auto-test on page load
        document.addEventListener('DOMContentLoaded', function() {
            showStatus('Test page loaded. Click the buttons above to test different features.', 'info');
        });
    </script>
</body>
</html>
