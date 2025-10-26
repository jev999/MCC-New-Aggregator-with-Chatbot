# MCC Gemini AI Chatbot Implementation

This document provides a complete guide for setting up and using the Gemini AI chatbot in the MCC News Aggregator system.

## 🚀 Overview

The Gemini AI chatbot is an advanced FAQ assistant powered by Google's Gemini AI that uses Retrieval-Augmented Generation (RAG) to provide accurate, contextual responses about MCC based on a comprehensive FAQ database.

## 📋 Features

- **Advanced AI**: Powered by Google's Gemini 1.5 Flash model
- **RAG Implementation**: Uses FAQ database for context-aware responses
- **Fallback System**: Graceful degradation when API is unavailable
- **Modern UI**: Beautiful, responsive chat interface
- **Comprehensive FAQ**: Detailed information about MCC programs, admissions, and services
- **Dual Chatbot Support**: Works alongside existing DeepSeek chatbot

## 🛠️ Installation & Setup

### 1. Install Gemini Package

The Gemini package has already been installed. If you need to reinstall:

```bash
composer require google-gemini-php/laravel
php artisan gemini:install
```

### 2. Configure Environment

Add your Gemini API key to your `.env` file:

```env
GEMINI_API_KEY=your_gemini_api_key_here
```

**To get your API key:**
1. Visit [Google AI Studio](https://aistudio.google.com/app/apikey)
2. Sign in with your Google account
3. Create a new API key
4. Copy the key and add it to your `.env` file

### 3. Clear Configuration Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## 📁 File Structure

```
app/
├── Services/
│   └── GeminiChatbotService.php          # Main service for Gemini API calls
├── Http/Controllers/
│   └── GeminiChatbotController.php       # Controller for chat endpoints

resources/views/components/
└── gemini-chatbot-widget.blade.php       # Chat interface component

storage/app/
└── faqs.txt                              # FAQ database

routes/
├── api.php                               # API routes for Gemini chatbot
└── web.php                               # Web routes including test page
```

## 🔧 API Endpoints

### Chat Endpoint
```
POST /api/gemini-chatbot
```
**Request:**
```json
{
    "message": "What programs does MCC offer?"
}
```

**Response:**
```json
{
    "success": true,
    "response": "MCC offers the following CHED-accredited programs...",
    "conversation_id": "default"
}
```

### Test Connection
```
GET /api/gemini-test
```

### Get FAQ Content
```
GET /api/gemini-faq
```

## 🎨 Frontend Integration

### Using the Chatbot Widget

Include the Gemini chatbot widget in any Blade template:

```blade
@include('components.gemini-chatbot-widget')
```

### Custom Integration

For custom integration, use the API endpoints:

```javascript
async function sendMessage(message) {
    const response = await fetch('/api/gemini-chatbot', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ message: message })
    });
    
    const data = await response.json();
    return data.response;
}
```

## 🧪 Testing

### Test Page

Visit `/test-chatbots` to access the comprehensive test interface that allows you to:

- Test both DeepSeek and Gemini chatbots side by side
- Verify API connections
- Check FAQ database status
- Compare response quality

### Manual Testing

1. **Test API Connection:**
   ```bash
   curl -X GET http://your-domain/api/gemini-test
   ```

2. **Test Chat Functionality:**
   ```bash
   curl -X POST http://your-domain/api/gemini-chatbot \
   -H "Content-Type: application/json" \
   -H "X-CSRF-TOKEN: your-csrf-token" \
   -d '{"message": "What is MCC?"}'
   ```

## 📚 FAQ Database

The FAQ database (`storage/app/faqs.txt`) contains comprehensive information about:

- **College Information**: Location, contact details, operating hours
- **Academic Programs**: BSIT, BSBA, BEED, BSED, BSHM details
- **Admission Requirements**: Application process, fees, requirements
- **Campus Facilities**: Library, labs, gymnasium, cafeteria
- **Student Services**: Registrar, guidance, scholarships
- **Events & Activities**: Foundation month, intramurals, IT days
- **Faculty Information**: BSIT instructors and staff
- **Contact Information**: Department-specific email addresses

### Updating FAQ Content

To update the FAQ database:

1. Edit `storage/app/faqs.txt`
2. Follow the Q&A format:
   ```
   Q: Your question here?
   A: Your answer here.
   ```
3. The changes will be reflected immediately (no cache clearing needed)

## 🔄 How It Works

### RAG (Retrieval-Augmented Generation) Process

1. **User Input**: User sends a message
2. **Context Retrieval**: System loads FAQ database
3. **Prompt Construction**: Creates comprehensive prompt with FAQ context
4. **AI Processing**: Gemini AI processes the prompt with context
5. **Response Generation**: AI generates contextual response
6. **Response Cleaning**: System formats and cleans the response
7. **Fallback**: If API fails, uses keyword-based fallback responses

### System Prompt

The system uses a detailed prompt that instructs the AI to:
- Answer based only on provided FAQ content
- Be concise but helpful
- Use bold formatting for important details
- Maintain a friendly, professional tone
- Direct users to official contacts when needed

## 🚨 Error Handling

### API Failures
- Automatic retry logic (up to 2 retries)
- Graceful fallback to keyword-based responses
- Comprehensive error logging

### Fallback Responses
When the Gemini API is unavailable, the system provides intelligent fallback responses based on keyword matching for common queries like:
- Greetings
- Program information
- Admission requirements
- Contact information
- Location details

## 🔧 Configuration Options

### Gemini API Settings

In `config/gemini.php`:

```php
'api_key' => env('GEMINI_API_KEY'),
'base_url' => env('GEMINI_BASE_URL'),
'request_timeout' => env('GEMINI_REQUEST_TIMEOUT', 30),
```

### Service Configuration

In `GeminiChatbotService.php`, you can adjust:
- Model parameters (temperature, topK, topP)
- Safety settings
- Response length limits
- Retry logic

## 📊 Performance Considerations

- **Caching**: FAQ content is loaded from file (no database queries)
- **Timeout**: 30-second timeout for API calls
- **Retry Logic**: Up to 2 retries with 1-second delays
- **Response Limits**: Max 1024 tokens for responses

## 🔒 Security Features

- **CSRF Protection**: All API endpoints protected
- **Input Validation**: Message length and content validation
- **Safety Settings**: Content filtering for harmful content
- **Error Logging**: Comprehensive logging without exposing sensitive data

## 🆚 Comparison: DeepSeek vs Gemini

| Feature | DeepSeek | Gemini |
|---------|----------|---------|
| **AI Model** | DeepSeek Chat | Gemini 1.5 Flash |
| **Context** | System prompt only | RAG with FAQ database |
| **Response Quality** | Good | Excellent (context-aware) |
| **Fallback System** | Keyword-based | Keyword-based + AI |
| **API Reliability** | Good | Excellent |
| **Cost** | Lower | Higher |
| **Response Time** | Fast | Moderate |

## 🚀 Deployment

### Production Checklist

1. ✅ Add `GEMINI_API_KEY` to production `.env`
2. ✅ Ensure FAQ file is deployed (`storage/app/faqs.txt`)
3. ✅ Test API connection in production
4. ✅ Verify chatbot widget appears on pages
5. ✅ Test fallback responses work
6. ✅ Monitor error logs

### Environment Variables

```env
# Required
GEMINI_API_KEY=your_production_api_key

# Optional
GEMINI_BASE_URL=
GEMINI_REQUEST_TIMEOUT=30
```

## 🐛 Troubleshooting

### Common Issues

1. **"API key not configured"**
   - Check `.env` file has `GEMINI_API_KEY`
   - Run `php artisan config:clear`

2. **"API connection failed"**
   - Verify API key is valid
   - Check internet connectivity
   - Review error logs

3. **"FAQ database not found"**
   - Ensure `storage/app/faqs.txt` exists
   - Check file permissions

4. **Chatbot not appearing**
   - Include the widget component in your template
   - Check for JavaScript errors in console

### Debug Commands

```bash
# Test API connection
php artisan tinker
>>> app(App\Services\GeminiChatbotService::class)->generateResponse('Hello', 'Test context');

# Check FAQ content
php artisan tinker
>>> Storage::get('faqs.txt');
```

## 📈 Future Enhancements

Potential improvements for the Gemini chatbot:

1. **Database Integration**: Move FAQ to database for easier management
2. **User Feedback**: Add thumbs up/down for response quality
3. **Analytics**: Track popular questions and response effectiveness
4. **Multi-language**: Support for multiple languages
5. **Voice Interface**: Add speech-to-text capabilities
6. **File Upload**: Allow users to upload documents for analysis

## 📞 Support

For technical support or questions about the Gemini chatbot implementation:

- **Email**: info@mcc-nac.edu.ph
- **Documentation**: This file and inline code comments
- **Test Interface**: `/test-chatbots` for debugging

---

**Implementation Date**: December 2024  
**Version**: 1.0  
**Status**: Production Ready ✅
