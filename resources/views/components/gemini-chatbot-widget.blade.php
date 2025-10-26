<div id="gemini-chatbot-widget" class="gemini-chatbot-widget">
    <button id="gemini-chatbot-toggle" class="gemini-chatbot-toggle minimized" title="Open MCC-NAC Assistant">
        <div class="chatbot-icon">
            <i class="fas fa-robot"></i>
        </div>
        <div class="chatbot-pulse"></div>
    </button>
    
    <div id="gemini-chatbot-container" class="gemini-chatbot-container">
        <div class="gemini-chatbot-header">
            <div class="gemini-chatbot-title">
                <div class="header-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="header-info">
                    <h3>MCC-NAC Assistant</h3>
                    <span class="status-indicator">
                        <span class="status-dot"></span>
                        Online
                    </span>
                </div>
            </div>
            <div class="header-actions">
                <button class="header-btn" onclick="clearChatHistory()" title="Clear Chat">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button class="header-btn minimize-btn" onclick="toggleGeminiChatbot()" title="Minimize">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        
        <div class="gemini-chatbot-messages" id="gemini-chatbot-messages">
            <div class="message bot-message welcome-message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        <p>👋 Hello! I'm your MCC-NAC Assistant, powered by advanced AI. I can help you with accurate information about:</p>
                        <ul class="quick-help">
                            <li>🎓 Academic programs (BSIT, BSBA, BEED, BSED, BSHM)</li>
                            <li>📝 Admissions & enrollment requirements</li>
                            <li>💰 Tuition-free education details</li>
                            <li>📞 Contact information & office hours</li>
                            <li>🏫 Campus facilities & services</li>
                            <li>👨‍🏫 Faculty information</li>
                        </ul>
                        <p>What would you like to know about Madridejos Community College?</p>
                    </div>
                    <span class="message-time">Just now</span>
                </div>
            </div>
        </div>
        
        <div class="gemini-chatbot-input">
            <div class="input-container">
                <div class="input-wrapper">
                    <input type="text" id="gemini-chatbot-input" placeholder="Ask me about MCC programs, admissions, fees..." maxlength="500">
                    <button id="gemini-chatbot-send" onclick="sendGeminiMessage()" class="send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <div class="quick-actions">
                    <button class="quick-btn" onclick="sendQuickMessage('What programs does MCC offer?')">
                        <i class="fas fa-graduation-cap"></i>
                        Programs
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('How do I apply for admission?')">
                        <i class="fas fa-file-alt"></i>
                        Admissions
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('What are the contact details?')">
                        <i class="fas fa-phone"></i>
                        Contact
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced Gemini Chatbot Widget Styles */
.gemini-chatbot-widget {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1500;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Enhanced Toggle Button */
.gemini-chatbot-toggle {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    font-size: 1rem;
    position: relative;
    overflow: hidden;
    min-width: 200px;
    /* Enhanced touch targets for mobile */
    min-height: 44px;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

/* Minimized Toggle Button */
.gemini-chatbot-toggle.minimized {
    width: 60px;
    height: 60px;
    min-width: 60px;
    padding: 0;
    border-radius: 50%;
    gap: 0;
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
}

.gemini-chatbot-toggle.minimized:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 35px rgba(5, 150, 105, 0.5);
}

.gemini-chatbot-toggle.minimized:active {
    transform: translateY(-1px) scale(0.95);
}

.gemini-chatbot-toggle:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
}

.gemini-chatbot-toggle:active {
    transform: translateY(-2px) scale(0.98);
}

.chatbot-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

/* Minimized chatbot icon */
.gemini-chatbot-toggle.minimized .chatbot-icon {
    width: 100%;
    height: 100%;
    background: transparent;
    border: none;
    backdrop-filter: none;
    font-size: 1.5rem;
}

.chatbot-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
}

/* Hide text in minimized version */
.gemini-chatbot-toggle.minimized .chatbot-text {
    display: none;
}

.chatbot-label {
    font-size: 1rem;
    font-weight: 600;
    line-height: 1;
}

.chatbot-status {
    font-size: 0.75rem;
    opacity: 0.9;
    font-weight: 400;
}

.chatbot-pulse {
    position: absolute;
    top: 50%;
    right: 1rem;
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    transform: translateY(-50%);
    animation: pulse 2s infinite;
}

/* Pulse positioning for minimized version */
.gemini-chatbot-toggle.minimized .chatbot-pulse {
    top: 8px;
    right: 8px;
    width: 10px;
    height: 10px;
    transform: none;
    background: #ffffff;
    box-shadow: 0 0 0 2px #059669;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}

/* Enhanced Container */
.gemini-chatbot-container {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 420px;
    height: 600px;
    background: white;
    border-radius: 24px;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: none;
    flex-direction: column;
    overflow: hidden;
    backdrop-filter: blur(20px);
}

.gemini-chatbot-container.active {
    display: flex;
    animation: chatbotSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes chatbotSlideIn {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Enhanced Header */
.gemini-chatbot-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.gemini-chatbot-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    backdrop-filter: blur(10px);
}

.gemini-chatbot-title {
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
    z-index: 1;
}

.header-avatar {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.header-info h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.2;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    opacity: 0.9;
    margin-top: 0.25rem;
}

.status-dot {
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
    position: relative;
    z-index: 1;
}

.header-btn {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.header-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

/* Enhanced Messages Area */
.gemini-chatbot-messages {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    /* Enhanced mobile scrolling */
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}

.gemini-chatbot-messages::-webkit-scrollbar {
    width: 6px;
}

.gemini-chatbot-messages::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.gemini-chatbot-messages::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.gemini-chatbot-messages::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Enhanced Message Styles */
.message {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
    margin-top: 0.25rem;
}

.bot-message .message-avatar {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.user-message .message-avatar {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.message-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.message-bubble {
    background: white;
    padding: 1rem 1.25rem;
    border-radius: 18px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    position: relative;
    max-width: 85%;
}

.user-message .message-bubble {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    margin-left: auto;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.bot-message .message-bubble {
    background: white;
    color: #374151;
}

.welcome-message .message-bubble {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border: 1px solid #bae6fd;
}

.message-bubble p {
    margin: 0 0 0.75rem 0;
    line-height: 1.5;
    font-size: 0.9rem;
}

.message-bubble p:last-child {
    margin-bottom: 0;
}

.quick-help {
    list-style: none;
    padding: 0;
    margin: 0.75rem 0;
}

.quick-help li {
    padding: 0.5rem 0;
    font-size: 0.85rem;
    color: #4b5563;
    border-bottom: 1px solid #e5e7eb;
}

.quick-help li:last-child {
    border-bottom: none;
}

.message-time {
    font-size: 0.75rem;
    color: #9ca3af;
    align-self: flex-end;
    margin-top: 0.25rem;
}

.user-message .message-time {
    color: rgba(255, 255, 255, 0.7);
}

/* Enhanced Input Area */
.gemini-chatbot-input {
    padding: 1.5rem;
    background: white;
    border-top: 1px solid #e2e8f0;
}

.input-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.input-wrapper {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.gemini-chatbot-input input {
    flex: 1;
    padding: 1rem 1.25rem;
    border: 2px solid #e2e8f0;
    border-radius: 25px;
    font-size: 0.9rem;
    outline: none;
    transition: all 0.3s ease;
    background: #f8fafc;
    font-family: inherit;
    /* Enhanced mobile input handling */
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    resize: none;
    min-height: 44px;
}

.gemini-chatbot-input input:focus {
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.send-btn {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    /* Enhanced mobile touch targets */
    min-width: 44px;
    min-height: 44px;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

.send-btn:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.send-btn:active {
    transform: translateY(0) scale(0.95);
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Quick Actions */
.quick-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.quick-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    font-size: 0.8rem;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    /* Enhanced mobile touch targets */
    min-height: 44px;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

.quick-btn:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
    transform: translateY(-1px);
}

.quick-btn i {
    font-size: 0.75rem;
}

/* Typing Indicator */
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    max-width: 120px;
}

.typing-dots {
    display: flex;
    gap: 0.25rem;
}

.typing-dot {
    width: 6px;
    height: 6px;
    background: #9ca3af;
    border-radius: 50%;
    animation: typingDot 1.4s infinite ease-in-out;
}

.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes typingDot {
    0%, 80%, 100% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Enhanced Responsive Design */
@media (max-width: 768px) {
    .gemini-chatbot-widget {
        bottom: 1rem;
        right: 1rem;
    }
    
    .gemini-chatbot-container {
        width: min(380px, calc(100vw - 2rem));
        height: min(550px, calc(100vh - 8rem));
        bottom: 80px;
        right: 0;
        max-height: 80vh;
    }
    
    .gemini-chatbot-toggle {
        min-width: 160px;
        padding: 0.875rem 1.25rem;
    }
    
    .gemini-chatbot-toggle.minimized {
        width: 56px;
        height: 56px;
        min-width: 56px;
    }
    
    .chatbot-label {
        font-size: 0.9rem;
    }
    
    .chatbot-status {
        font-size: 0.7rem;
    }
    
    .gemini-chatbot-header {
        padding: 1.25rem;
    }
    
    .gemini-chatbot-messages {
        padding: 1.25rem;
    }
    
    .gemini-chatbot-input {
        padding: 1.25rem;
    }
    
    .message-bubble {
        max-width: 90%;
        padding: 0.875rem 1rem;
    }
    
    .quick-actions {
        gap: 0.375rem;
    }
    
    .quick-btn {
        padding: 0.625rem 0.875rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 640px) {
    .gemini-chatbot-widget {
        bottom: 0.75rem;
        right: 0.75rem;
    }
    
    .gemini-chatbot-container {
        width: calc(100vw - 1.5rem);
        max-width: 360px;
        height: min(500px, calc(100vh - 6rem));
        bottom: 70px;
        right: 0;
        max-height: 75vh;
    }
    
    .gemini-chatbot-toggle {
        min-width: 150px;
        padding: 0.75rem 1rem;
        gap: 0.75rem;
    }
    
    .gemini-chatbot-toggle.minimized {
        width: 52px;
        height: 52px;
        min-width: 52px;
    }
    
    .chatbot-icon {
        width: 38px;
        height: 38px;
        font-size: 1.1rem;
    }
    
    .chatbot-label {
        font-size: 0.875rem;
    }
    
    .chatbot-status {
        font-size: 0.7rem;
    }
    
    .gemini-chatbot-header {
        padding: 1rem;
    }
    
    .header-avatar {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
    
    .header-info h3 {
        font-size: 1rem;
    }
    
    .gemini-chatbot-messages {
        padding: 1rem;
        gap: 1rem;
    }
    
    .message-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
    
    .message-bubble {
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        max-width: 88%;
    }
    
    .gemini-chatbot-input {
        padding: 1rem;
    }
    
    .gemini-chatbot-input input {
        padding: 0.875rem 1rem;
        font-size: 0.85rem;
    }
    
    .send-btn {
        width: 44px;
        height: 44px;
        font-size: 0.9rem;
    }
    
    .quick-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .quick-btn {
        justify-content: center;
        padding: 0.75rem 1rem;
        font-size: 0.8rem;
    }
}

@media (max-width: 480px) {
    .gemini-chatbot-widget {
        bottom: 0.5rem;
        right: 0.5rem;
    }
    
    .gemini-chatbot-container {
        width: calc(100vw - 1rem);
        max-width: 340px;
        height: min(450px, calc(100vh - 4rem));
        bottom: 60px;
        right: 0;
        max-height: 70vh;
        border-radius: 20px;
    }
    
    .gemini-chatbot-toggle {
        min-width: 130px;
        padding: 0.625rem 0.875rem;
        gap: 0.625rem;
        border-radius: 40px;
    }
    
    .gemini-chatbot-toggle.minimized {
        width: 48px;
        height: 48px;
        min-width: 48px;
    }
    
    .chatbot-icon {
        width: 34px;
        height: 34px;
        font-size: 1rem;
    }
    
    .chatbot-label {
        font-size: 0.8rem;
    }
    
    .chatbot-status {
        font-size: 0.65rem;
    }
    
    .gemini-chatbot-header {
        padding: 0.875rem;
        border-radius: 20px 20px 0 0;
    }
    
    .header-avatar {
        width: 36px;
        height: 36px;
        font-size: 1.1rem;
    }
    
    .header-info h3 {
        font-size: 0.95rem;
    }
    
    .status-indicator {
        font-size: 0.75rem;
    }
    
    .header-btn {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
    
    .gemini-chatbot-messages {
        padding: 0.875rem;
        gap: 0.875rem;
    }
    
    .message-avatar {
        width: 30px;
        height: 30px;
        font-size: 0.75rem;
    }
    
    .message-bubble {
        padding: 0.625rem 0.875rem;
        font-size: 0.8rem;
        max-width: 85%;
        border-radius: 16px;
    }
    
    .message-bubble p {
        font-size: 0.8rem;
        line-height: 1.4;
    }
    
    .quick-help li {
        font-size: 0.75rem;
        padding: 0.375rem 0;
    }
    
    .message-time {
        font-size: 0.7rem;
    }
    
    .gemini-chatbot-input {
        padding: 0.875rem;
    }
    
    .gemini-chatbot-input input {
        padding: 0.75rem 0.875rem;
        font-size: 0.8rem;
        border-radius: 20px;
    }
    
    .send-btn {
        width: 40px;
        height: 40px;
        font-size: 0.85rem;
    }
    
    .quick-actions {
        gap: 0.375rem;
    }
    
    .quick-btn {
        padding: 0.625rem 0.875rem;
        font-size: 0.75rem;
        border-radius: 16px;
    }
    
    .quick-btn i {
        font-size: 0.7rem;
    }
}

@media (max-width: 360px) {
    .gemini-chatbot-container {
        width: calc(100vw - 0.75rem);
        max-width: 320px;
        height: min(400px, calc(100vh - 3rem));
        bottom: 55px;
        max-height: 65vh;
    }
    
    .gemini-chatbot-toggle {
        min-width: 120px;
        padding: 0.5rem 0.75rem;
        gap: 0.5rem;
    }
    
    .gemini-chatbot-toggle.minimized {
        width: 44px;
        height: 44px;
        min-width: 44px;
    }
    
    .chatbot-icon {
        width: 32px;
        height: 32px;
        font-size: 0.9rem;
    }
    
    .chatbot-label {
        font-size: 0.75rem;
    }
    
    .chatbot-status {
        font-size: 0.6rem;
    }
    
    .gemini-chatbot-header {
        padding: 0.75rem;
    }
    
    .header-avatar {
        width: 32px;
        height: 32px;
        font-size: 1rem;
    }
    
    .header-info h3 {
        font-size: 0.9rem;
    }
    
    .gemini-chatbot-messages {
        padding: 0.75rem;
    }
    
    .message-bubble {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .gemini-chatbot-input {
        padding: 0.75rem;
    }
    
    .gemini-chatbot-input input {
        padding: 0.625rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .send-btn {
        width: 36px;
        height: 36px;
        font-size: 0.8rem;
    }
}

/* Landscape mobile optimization */
@media (max-width: 768px) and (orientation: landscape) {
    .gemini-chatbot-container {
        height: min(400px, calc(100vh - 4rem));
        max-height: 60vh;
    }
    
    .gemini-chatbot-messages {
        padding: 0.75rem;
    }
    
    .gemini-chatbot-input {
        padding: 0.75rem;
    }
    
    .quick-actions {
        flex-direction: row;
        flex-wrap: wrap;
    }
    
    .quick-btn {
        flex: 1;
        min-width: 0;
        padding: 0.5rem 0.75rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .gemini-chatbot-container {
        background: #1e293b;
        border-color: #334155;
    }
    
    .gemini-chatbot-messages {
        background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    }
    
    .message-bubble {
        background: #334155;
        color: #e2e8f0;
        border-color: #475569;
    }
    
    .welcome-message .message-bubble {
        background: linear-gradient(135deg, #1e3a8a, #1e40af);
        border-color: #3b82f6;
    }
    
    .gemini-chatbot-input {
        background: #1e293b;
        border-color: #334155;
    }
    
    .gemini-chatbot-input input {
        background: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }
    
    .gemini-chatbot-input input:focus {
        background: #475569;
        border-color: #667eea;
    }
    
    .quick-btn {
        background: #334155;
        border-color: #475569;
        color: #cbd5e1;
    }
    
    .quick-btn:hover {
        background: #667eea;
        color: white;
    }
}
</style>

<script>
// Enhanced Gemini Chatbot functionality
let conversationHistory = [];
let isTyping = false;

function toggleGeminiChatbot() {
    const container = document.getElementById('gemini-chatbot-container');
    const toggle = document.getElementById('gemini-chatbot-toggle');
    
    container.classList.toggle('active');
    
    if (container.classList.contains('active')) {
        // Focus input when opened
        setTimeout(() => {
            document.getElementById('gemini-chatbot-input').focus();
        }, 300);
    }
}

async function sendGeminiMessage() {
    const input = document.getElementById('gemini-chatbot-input');
    const message = input.value.trim();
    
    if (!message || isTyping) return;
    
    // Add user message
    addGeminiMessage(message, 'user');
    conversationHistory.push({ role: 'user', content: message });
    input.value = '';
    
    // Disable input while processing
    setInputState(true);
    
    // Show enhanced typing indicator
    showEnhancedTypingIndicator();
    
    try {
        // Use Gemini chatbot route
        const response = await fetch('{{ route("api.gemini.chatbot") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                conversation_history: conversationHistory.slice(-10) // Send last 10 messages for context
            })
        });
        
        const data = await response.json();
        
        hideTypingIndicator();
        
        if (data.success) {
            addGeminiMessage(data.response, 'bot');
            conversationHistory.push({ role: 'bot', content: data.response });
        } else {
            addGeminiMessage(data.response || 'Sorry, I encountered an issue. Please try again.', 'bot');
        }
        
    } catch (error) {
        hideTypingIndicator();
        console.error('Gemini Chatbot error:', error);
        
        // Enhanced fallback response
        const fallbackResponses = [
            "I'm having trouble connecting to the Gemini AI service right now. For immediate assistance, please contact us at <strong>info@mcc-nac.edu.ph</strong> or call <strong>(032) 123-4567</strong>.",
            "Sorry, I'm experiencing technical difficulties with the AI service. You can reach MCC directly at <strong>info@mcc-nac.edu.ph</strong> for help with admissions, programs, or any questions.",
            "I'm currently offline, but you can contact our office at <strong>info@mcc-nac.edu.ph</strong> for any questions about MCC programs, admissions, or campus information."
        ];
        
        const randomResponse = fallbackResponses[Math.floor(Math.random() * fallbackResponses.length)];
        addGeminiMessage(randomResponse, 'bot');
    } finally {
        // Re-enable input
        setInputState(false);
        input.focus();
    }
}

function sendQuickMessage(message) {
    const input = document.getElementById('gemini-chatbot-input');
    input.value = message;
    sendGeminiMessage();
}

function addGeminiMessage(text, sender) {
    const messagesContainer = document.getElementById('gemini-chatbot-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message`;
    
    const now = new Date();
    const timeStr = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    // Enhanced message bubble with better formatting
    const messageBubble = `
        <div class="message-bubble">
            ${formatMessageContent(text)}
        </div>
    `;
    
    messageDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-${sender === 'user' ? 'user' : 'robot'}"></i>
        </div>
        <div class="message-content">
            ${messageBubble}
            <span class="message-time">${timeStr}</span>
        </div>
    `;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // Add subtle animation
    messageDiv.style.opacity = '0';
    messageDiv.style.transform = 'translateY(10px)';
    setTimeout(() => {
        messageDiv.style.transition = 'all 0.3s ease';
        messageDiv.style.opacity = '1';
        messageDiv.style.transform = 'translateY(0)';
    }, 50);
}

function formatMessageContent(text) {
    // Convert line breaks to HTML
    text = text.replace(/\n/g, '<br>');
    
    // Format bold text
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    
    // Format italic text
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    
    // Format lists
    text = text.replace(/^•\s(.+)$/gm, '<li>$1</li>');
    text = text.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
    
    // Format paragraphs
    const paragraphs = text.split('<br><br>');
    return paragraphs.map(p => `<p>${p}</p>`).join('');
}

function showEnhancedTypingIndicator() {
    isTyping = true;
    const messagesContainer = document.getElementById('gemini-chatbot-messages');
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typing-indicator';
    typingDiv.className = 'message bot-message';
    typingDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="message-content">
            <div class="typing-indicator">
                <span>MCC-NAC Assistant is thinking</span>
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>
    `;
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function hideTypingIndicator() {
    isTyping = false;
    const indicator = document.getElementById('typing-indicator');
    if (indicator) {
        indicator.remove();
    }
}

function setInputState(disabled) {
    const input = document.getElementById('gemini-chatbot-input');
    const sendBtn = document.getElementById('gemini-chatbot-send');
    
    input.disabled = disabled;
    sendBtn.disabled = disabled;
    
    if (disabled) {
        input.placeholder = 'MCC-NAC Assistant is thinking...';
        sendBtn.style.opacity = '0.5';
    } else {
        input.placeholder = 'Ask me about MCC programs, admissions, fees...';
        sendBtn.style.opacity = '1';
    }
}

function clearChatHistory() {
    if (confirm('Are you sure you want to clear the chat history?')) {
        const messagesContainer = document.getElementById('gemini-chatbot-messages');
        
        // Keep only the welcome message
        const welcomeMessage = messagesContainer.querySelector('.welcome-message');
        messagesContainer.innerHTML = '';
        if (welcomeMessage) {
            messagesContainer.appendChild(welcomeMessage);
        }
        
        // Clear conversation history
        conversationHistory = [];
        
        // Show confirmation message
        setTimeout(() => {
            addGeminiMessage('Chat history cleared! What would you like to know about MCC?', 'bot');
        }, 500);
    }
}

// Enhanced event listeners with mobile optimizations
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('gemini-chatbot-input');
    const sendBtn = document.getElementById('gemini-chatbot-send');
    const toggle = document.getElementById('gemini-chatbot-toggle');
    const container = document.getElementById('gemini-chatbot-container');
    
    // Detect if device is mobile
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                     window.innerWidth <= 768;
    
    // Enter key to send message
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendGeminiMessage();
        }
    });
    
    // Auto-resize input with mobile considerations
    input.addEventListener('input', function() {
        this.style.height = 'auto';
        const maxHeight = isMobile ? 80 : 120;
        this.style.height = Math.min(this.scrollHeight, maxHeight) + 'px';
    });
    
    // Send button click with mobile touch feedback
    sendBtn.addEventListener('click', function(e) {
        e.preventDefault();
        // Add touch feedback for mobile
        if (isMobile) {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        }
        sendGeminiMessage();
    });
    
    // Toggle button click with mobile touch feedback
    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        // Add touch feedback for mobile
        if (isMobile) {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        }
        toggleGeminiChatbot();
    });
    
    // Enhanced click outside to close (mobile-friendly)
    document.addEventListener('click', function(e) {
        if (container.classList.contains('active') && 
            !container.contains(e.target) && 
            !toggle.contains(e.target)) {
            container.classList.remove('active');
        }
    });
    
    // Touch events for better mobile interaction
    if (isMobile) {
        // Prevent zoom on input focus
        input.addEventListener('focus', function() {
            document.querySelector('meta[name="viewport"]').setAttribute('content', 
                'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
        });
        
        input.addEventListener('blur', function() {
            document.querySelector('meta[name="viewport"]').setAttribute('content', 
                'width=device-width, initial-scale=1.0');
        });
        
        // Add touch feedback to quick buttons
        const quickBtns = document.querySelectorAll('.quick-btn');
        quickBtns.forEach(btn => {
            btn.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.95)';
                this.style.backgroundColor = '#667eea';
                this.style.color = 'white';
            });
            
            btn.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.style.transform = '';
                    this.style.backgroundColor = '';
                    this.style.color = '';
                }, 150);
            });
        });
        
        // Prevent body scroll when chatbot is open
        const originalBodyStyle = document.body.style.overflow;
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if (container.classList.contains('active')) {
                        document.body.style.overflow = 'hidden';
                        // Focus input after animation
                        setTimeout(() => {
                            input.focus();
                        }, 300);
                    } else {
                        document.body.style.overflow = originalBodyStyle;
                    }
                }
            });
        });
        
        observer.observe(container, {
            attributes: true,
            attributeFilter: ['class']
        });
    } else {
        // Desktop behavior
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if (container.classList.contains('active')) {
                        setTimeout(() => {
                            input.focus();
                        }, 300);
                    }
                }
            });
        });
        
        observer.observe(container, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
    
    // Keyboard shortcuts (desktop only)
    if (!isMobile) {
        document.addEventListener('keydown', function(e) {
            // Escape to close
            if (e.key === 'Escape') {
                if (container.classList.contains('active')) {
                    container.classList.remove('active');
                }
            }
        });
    }
    
    // Handle orientation change for mobile
    if (isMobile) {
        window.addEventListener('orientationchange', function() {
            setTimeout(() => {
                if (container.classList.contains('active')) {
                    // Recalculate container position
                    const rect = container.getBoundingClientRect();
                    if (rect.bottom > window.innerHeight) {
                        container.style.bottom = '10px';
                    }
                }
            }, 100);
        });
    }
    
    // Smooth scrolling for messages
    const messagesContainer = document.getElementById('gemini-chatbot-messages');
    if (messagesContainer) {
        messagesContainer.style.scrollBehavior = 'smooth';
    }
});

// Add some personality to the chatbot
function getRandomGreeting() {
    const greetings = [
        "Hello! 👋 How can I help you today?",
        "Hi there! 😊 What would you like to know about MCC?",
        "Welcome! 🎓 I'm here to assist you with MCC information.",
        "Good day! ☀️ How can I make your MCC experience better?"
    ];
    return greetings[Math.floor(Math.random() * greetings.length)];
}

// Show random greeting on first interaction
let hasInteracted = false;
document.getElementById('gemini-chatbot-input').addEventListener('focus', function() {
    if (!hasInteracted) {
        hasInteracted = true;
        // Could add a subtle greeting animation here
    }
});
</script>
