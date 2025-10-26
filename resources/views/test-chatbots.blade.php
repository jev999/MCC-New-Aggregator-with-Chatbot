<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MCC Chatbot Test - DeepSeek vs Gemini</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .chatbots-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .chatbot-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        
        .chatbot-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .deepseek-icon {
            color: #667eea;
        }
        
        .gemini-icon {
            color: #4285f4;
        }
        
        .chatbot-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2d3748;
        }
        
        .chatbot-description {
            color: #718096;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .test-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .test-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .gemini-test-button {
            background: linear-gradient(135deg, #4285f4, #34a853);
        }
        
        .gemini-test-button:hover {
            box-shadow: 0 8px 25px rgba(66, 133, 244, 0.3);
        }
        
        .api-test-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
        }
        
        .api-test-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2d3748;
        }
        
        .test-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .test-result {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            background: #f7fafc;
            border-left: 4px solid #4299e1;
            display: none;
        }
        
        .test-result.show {
            display: block;
        }
        
        .test-result.success {
            background: #f0fff4;
            border-left-color: #48bb78;
        }
        
        .test-result.error {
            background: #fed7d7;
            border-left-color: #f56565;
        }
        
        .instructions {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }
        
        .instructions h3 {
            color: #2d3748;
            margin-bottom: 1rem;
        }
        
        .instructions ol {
            color: #718096;
            line-height: 1.8;
        }
        
        .instructions li {
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .chatbots-grid {
                grid-template-columns: 1fr;
            }
            
            .test-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-robot"></i> MCC Chatbot Test Center</h1>
            <p>Compare DeepSeek AI vs Google Gemini AI for MCC FAQ assistance</p>
        </div>
        
        <div class="chatbots-grid">
            <div class="chatbot-card">
                <div class="chatbot-icon deepseek-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <h2 class="chatbot-title">DeepSeek AI</h2>
                <p class="chatbot-description">
                    MCC-NAC Assistant powered by DeepSeek AI. Provides accurate, consistent responses about MCC with comprehensive fallback system.
                </p>
                <a href="#" class="test-button" onclick="testDeepSeek()">
                    <i class="fas fa-play"></i> Test DeepSeek
                </a>
            </div>
            
            <div class="chatbot-card">
                <div class="chatbot-icon gemini-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h2 class="chatbot-title">Google Gemini AI</h2>
                <p class="chatbot-description">
                    MCC-NAC Assistant powered by Google's Gemini AI. Uses RAG (Retrieval-Augmented Generation) with comprehensive MCC FAQ database for accurate responses.
                </p>
                <a href="#" class="test-button gemini-test-button" onclick="testGemini()">
                    <i class="fas fa-play"></i> Test Gemini
                </a>
            </div>
        </div>
        
        <div class="api-test-section">
            <h3 class="api-test-title">API Connection Tests</h3>
            <div class="test-buttons">
                <button class="test-button" onclick="testDeepSeekConnection()">
                    <i class="fas fa-plug"></i> Test DeepSeek API
                </button>
                <button class="test-button gemini-test-button" onclick="testGeminiConnection()">
                    <i class="fas fa-plug"></i> Test Gemini API
                </button>
                <button class="test-button" onclick="testGeminiFAQ()">
                    <i class="fas fa-database"></i> Test FAQ Database
                </button>
            </div>
            <div id="test-result" class="test-result"></div>
        </div>
        
        <div class="instructions">
            <h3><i class="fas fa-info-circle"></i> How to Use</h3>
            <ol>
                <li><strong>Test API Connections:</strong> Click the API test buttons above to verify both services are working</li>
                <li><strong>Compare Responses:</strong> Ask the same question to both chatbots to see the difference in responses</li>
                <li><strong>Sample Questions:</strong> Try asking about "MCC programs", "admission requirements", "contact information", or "BSIT faculty"</li>
                <li><strong>Gemini Features:</strong> The Gemini chatbot has access to a comprehensive FAQ database and should provide more detailed, contextual responses</li>
                <li><strong>Fallback System:</strong> Both chatbots have fallback responses if their respective APIs are unavailable</li>
            </ol>
        </div>
    </div>
    
    <!-- Include Gemini chatbot widget -->
    @include('components.gemini-chatbot-widget')
    
    <script>
        function showTestResult(message, type = 'info') {
            const resultDiv = document.getElementById('test-result');
            resultDiv.className = `test-result show ${type}`;
            resultDiv.innerHTML = message;
        }
        
        async function testDeepSeekConnection() {
            showTestResult('<i class="fas fa-spinner fa-spin"></i> Testing DeepSeek API connection...', 'info');
            
            try {
                const response = await fetch('/test-deepseek');
                const data = await response.json();
                
                if (data.success) {
                    showTestResult(`
                        <strong><i class="fas fa-check-circle"></i> DeepSeek API Connection Successful!</strong><br>
                        Status: ${data.status}<br>
                        Response: ${data.response?.choices?.[0]?.message?.content || 'API responded successfully'}
                    `, 'success');
                } else {
                    showTestResult(`
                        <strong><i class="fas fa-exclamation-triangle"></i> DeepSeek API Connection Failed</strong><br>
                        Error: ${data.error || 'Unknown error'}<br>
                        Debug: ${JSON.stringify(data.debug, null, 2)}
                    `, 'error');
                }
            } catch (error) {
                showTestResult(`
                    <strong><i class="fas fa-times-circle"></i> DeepSeek API Test Failed</strong><br>
                    Error: ${error.message}
                `, 'error');
            }
        }
        
        async function testGeminiConnection() {
            showTestResult('<i class="fas fa-spinner fa-spin"></i> Testing Gemini API connection...', 'info');
            
            try {
                const response = await fetch('/api/gemini-test');
                const data = await response.json();
                
                if (data.success) {
                    showTestResult(`
                        <strong><i class="fas fa-check-circle"></i> Gemini API Connection Successful!</strong><br>
                        Message: ${data.message}<br>
                        Test Response: ${data.test_response}<br>
                        FAQ Context Loaded: ${data.faq_context_loaded ? 'Yes' : 'No'}
                    `, 'success');
                } else {
                    showTestResult(`
                        <strong><i class="fas fa-exclamation-triangle"></i> Gemini API Connection Failed</strong><br>
                        Error: ${data.error || data.message}<br>
                        ${data.instructions ? 'Instructions: ' + data.instructions.join('<br>') : ''}
                    `, 'error');
                }
            } catch (error) {
                showTestResult(`
                    <strong><i class="fas fa-times-circle"></i> Gemini API Test Failed</strong><br>
                    Error: ${error.message}
                `, 'error');
            }
        }
        
        async function testGeminiFAQ() {
            showTestResult('<i class="fas fa-spinner fa-spin"></i> Testing FAQ database...', 'info');
            
            try {
                const response = await fetch('/api/gemini-faq');
                const data = await response.json();
                
                if (data.success) {
                    showTestResult(`
                        <strong><i class="fas fa-check-circle"></i> FAQ Database Loaded Successfully!</strong><br>
                        Content Length: ${data.content_length} characters<br>
                        File Exists: ${data.file_exists ? 'Yes' : 'No'}<br>
                        <details style="margin-top: 10px;">
                            <summary>Preview FAQ Content</summary>
                            <pre style="background: #f7fafc; padding: 10px; border-radius: 5px; margin-top: 10px; max-height: 200px; overflow-y: auto;">${data.faq_content.substring(0, 500)}...</pre>
                        </details>
                    `, 'success');
                } else {
                    showTestResult(`
                        <strong><i class="fas fa-exclamation-triangle"></i> FAQ Database Test Failed</strong><br>
                        Error: ${data.error}
                    `, 'error');
                }
            } catch (error) {
                showTestResult(`
                    <strong><i class="fas fa-times-circle"></i> FAQ Database Test Failed</strong><br>
                    Error: ${error.message}
                `, 'error');
            }
        }
        
        function testDeepSeek() {
            // Open the original chatbot
            const container = document.getElementById('chatbot-container');
            container.classList.add('active');
        }
        
        function testGemini() {
            // Open the Gemini chatbot
            const container = document.getElementById('gemini-chatbot-container');
            container.classList.add('active');
        }
        
        // Auto-test connections on page load
        window.addEventListener('load', function() {
            setTimeout(() => {
                testDeepSeekConnection();
            }, 1000);
            
            setTimeout(() => {
                testGeminiConnection();
            }, 2000);
        });
    </script>
</body>
</html>
