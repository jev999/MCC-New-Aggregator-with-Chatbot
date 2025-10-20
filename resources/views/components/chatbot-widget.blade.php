<div id="chatbot-widget" class="chatbot-widget">
    <button id="chatbot-toggle" class="chatbot-toggle" title="Open MCC AI Assistant">
        <i class="fas fa-comments"></i>
        <span class="chatbot-badge">AI Help</span>
    </button>
    
    <div id="chatbot-container" class="chatbot-container">
        <div class="chatbot-header">
            <div class="chatbot-title">
                <i class="fas fa-robot"></i>
                <span>MCC AI Assistant</span>
            </div>
            <button class="chatbot-minimize" onclick="toggleChatbot()">
                <i class="fas fa-minus"></i>
            </button>
        </div>
        
        <div class="chatbot-messages" id="chatbot-messages">
            <div class="message bot-message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <p>Hello! I'm your MCC AI Assistant. How can I help you today?</p>
                    <span class="message-time">Now</span>
                </div>
            </div>
        </div>
        
        <div class="chatbot-input">
            <div class="input-container">
                <input type="text" id="chatbot-input" placeholder="Ask me about MCC programs, events, or anything else..." maxlength="500">
                <button id="chatbot-send" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Chatbot Widget Styles */
.chatbot-widget {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1500;
}

.chatbot-toggle {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    font-size: 1rem;
}

.chatbot-toggle:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.chatbot-badge {
    font-size: 0.875rem;
}

.chatbot-container {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 400px;
    height: 500px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    border: 1px solid #f1f5f9;
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.chatbot-container.active {
    display: flex;
    animation: chatbotSlideIn 0.3s ease;
}

@keyframes chatbotSlideIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.chatbot-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chatbot-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
}

.chatbot-minimize {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.chatbot-minimize:hover {
    background: rgba(255, 255, 255, 0.3);
}

.chatbot-messages {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.message {
    display: flex;
    gap: 0.75rem;
    max-width: 85%;
}

.bot-message {
    align-self: flex-start;
}

.user-message {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.user-message .message-avatar {
    background: linear-gradient(135deg, #10b981, #047857);
}

.message-content {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 16px;
    border-bottom-left-radius: 4px;
}

.user-message .message-content {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-bottom-right-radius: 4px;
    border-bottom-left-radius: 16px;
}

.message-content p {
    margin: 0;
    line-height: 1.5;
}

.message-time {
    font-size: 0.75rem;
    opacity: 0.6;
    margin-top: 0.5rem;
    display: block;
}

.chatbot-input {
    padding: 1.5rem;
    border-top: 1px solid #f1f5f9;
}

.chatbot-input .input-container {
    display: flex;
    gap: 0.75rem;
}

.chatbot-input input {
    flex: 1;
    padding: 0.875rem 1rem;
    border: 2px solid #f1f5f9;
    border-radius: 12px;
    font-size: 0.875rem;
    outline: none;
    transition: all 0.3s ease;
}

.chatbot-input input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.chatbot-input button {
    padding: 0.875rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-input button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.chatbot-input button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Responsive */
@media (max-width: 768px) {
    .chatbot-container {
        width: 350px;
    }
}

@media (max-width: 480px) {
    .chatbot-widget {
        bottom: 1rem;
        right: 1rem;
    }
    
    .chatbot-container {
        width: 320px;
        height: 450px;
    }
}
</style>

<script>
// Laravel-integrated chatbot functionality
function toggleChatbot() {
    const container = document.getElementById('chatbot-container');
    container.classList.toggle('active');
}

async function sendMessage() {
    const input = document.getElementById('chatbot-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add user message
    addMessage(message, 'user');
    input.value = '';
    
    // Disable input while processing
    input.disabled = true;
    document.getElementById('chatbot-send').disabled = true;
    
    // Show typing indicator
    showTypingIndicator();
    
    try {
        // Use Laravel route for chatbot
        const response = await fetch('{{ route("api.chatbot") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message
            })
        });
        
        const data = await response.json();
        
        hideTypingIndicator();
        
        if (data.success) {
            addMessage(data.response, 'bot');
        } else {
            addMessage(data.response || 'Sorry, I encountered an issue. Please try again.', 'bot');
        }
        
    } catch (error) {
        hideTypingIndicator();
        console.error('Chatbot error:', error);
        
        // Fallback response
        const fallbackResponses = [
            "I'm having trouble connecting right now. For immediate assistance, please contact us at info@mcc-nac.edu.ph.",
            "Sorry, I'm experiencing technical difficulties. You can reach MCC directly at info@mcc-nac.edu.ph for help.",
            "I'm currently offline, but you can contact our office at info@mcc-nac.edu.ph for any questions about MCC."
        ];
        
        const randomResponse = fallbackResponses[Math.floor(Math.random() * fallbackResponses.length)];
        addMessage(randomResponse, 'bot');
    } finally {
        // Re-enable input
        input.disabled = false;
        document.getElementById('chatbot-send').disabled = false;
        input.focus();
    }
}

function addMessage(text, sender) {
    const messagesContainer = document.getElementById('chatbot-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message`;
    
    const now = new Date();
    const timeStr = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    messageDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-${sender === 'user' ? 'user' : 'robot'}"></i>
        </div>
        <div class="message-content">
            <p>${text}</p>
            <span class="message-time">${timeStr}</span>
        </div>
    `;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function showTypingIndicator() {
    const messagesContainer = document.getElementById('chatbot-messages');
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typing-indicator';
    typingDiv.className = 'message bot-message';
    typingDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="message-content">
            <p>Typing...</p>
        </div>
    `;
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function hideTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) {
        indicator.remove();
    }
}

// Enter key to send message
document.getElementById('chatbot-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

// Initialize chatbot toggle
document.getElementById('chatbot-toggle').addEventListener('click', toggleChatbot);
</script>
