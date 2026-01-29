class MediCareChatbot {
    constructor() {
        this.isOpen = false;
        this.init();
    }

    init() {
        this.createChatbotHTML();
        this.bindEvents();
        this.addWelcomeMessage();
    }

    createChatbotHTML() {
        const chatbotHTML = `
            <div class="chatbot-container">
                <button class="chatbot-toggle" id="chatbot-toggle">💬</button>
                <div class="chatbot-window" id="chatbot-window">
                    <div class="chatbot-header">
                        MediCare Assistant
                    </div>
                    <div class="chatbot-messages" id="chatbot-messages">
                        <div class="typing-indicator" id="typing-indicator">
                            <div class="typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>
                    <div class="chatbot-input">
                        <input type="text" id="chatbot-input" placeholder="Type your message..." maxlength="200">
                        <button id="chatbot-send">➤</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    bindEvents() {
        const toggle = document.getElementById('chatbot-toggle');
        const sendBtn = document.getElementById('chatbot-send');
        const input = document.getElementById('chatbot-input');

        toggle.addEventListener('click', () => this.toggleChatbot());
        sendBtn.addEventListener('click', () => this.sendMessage());
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
    }

    toggleChatbot() {
        const window = document.getElementById('chatbot-window');
        const toggle = document.getElementById('chatbot-toggle');
        
        this.isOpen = !this.isOpen;
        window.style.display = this.isOpen ? 'flex' : 'none';
        toggle.innerHTML = this.isOpen ? '✕' : '💬';
        
        if (this.isOpen) {
            document.getElementById('chatbot-input').focus();
        }
    }

    addWelcomeMessage() {
        setTimeout(() => {
            this.addMessage('Hello! I\'m your MediCare assistant. How can I help you today?', 'bot');
        }, 500);
    }

    async sendMessage() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        this.addMessage(message, 'user');
        input.value = '';
        
        this.showTyping();
        
        try {
            const response = await fetch('chatbot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message })
            });
            
            const data = await response.json();
            
            setTimeout(() => {
                this.hideTyping();
                this.addMessage(data.response, 'bot');
            }, 1000);
            
        } catch (error) {
            this.hideTyping();
            this.addMessage('Sorry, I\'m having trouble right now. Please try again later.', 'bot');
        }
    }

    addMessage(text, sender) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        
        messageDiv.innerHTML = `<div class="message-content">${text}</div>`;
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    showTyping() {
        document.getElementById('typing-indicator').style.display = 'block';
        document.getElementById('chatbot-messages').scrollTop = document.getElementById('chatbot-messages').scrollHeight;
    }

    hideTyping() {
        document.getElementById('typing-indicator').style.display = 'none';
    }
}

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MediCareChatbot();
});
