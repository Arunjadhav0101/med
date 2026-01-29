class MediCareChatbot {
    constructor() {
        this.isOpen = false;
        this.currentMedicine = null;
        this.currentAction = null;
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
            this.addMessage('Hello! I\'m MediCare AI Assistant created by Arun Jadhav. I can help you buy medicines automatically and manage blood bank services. Just tell me what you need!', 'bot');
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
                this.handleResponse(data);
            }, 1000);
            
        } catch (error) {
            this.hideTyping();
            this.addMessage('Sorry, I\'m having trouble right now. Please try again later.', 'bot');
        }
    }

    handleResponse(data) {
        // Handle simple text response
        if (typeof data.response === 'string') {
            this.addMessage(data.response, 'bot');
            return;
        }

        // Handle complex response with actions
        this.addMessage(data.response, 'bot');
        
        if (data.action) {
            this.currentAction = data.action;
            this.currentMedicine = data.medicine || null;
            
            switch (data.action) {
                case 'add_to_cart':
                    this.showQuantityButtons();
                    break;
                case 'prescription_required':
                    this.showPrescriptionButtons();
                    break;
            }
        }
    }

    showQuantityButtons() {
        const buttonsHTML = `
            <div class="action-buttons">
                <button onclick="chatbot.selectQuantity(1)">1 Strip</button>
                <button onclick="chatbot.selectQuantity(2)">2 Strips</button>
                <button onclick="chatbot.selectQuantity(3)">3 Strips</button>
                <button onclick="chatbot.customQuantity()">Other</button>
            </div>
        `;
        this.addActionButtons(buttonsHTML);
    }

    showPrescriptionButtons() {
        const buttonsHTML = `
            <div class="action-buttons">
                <button onclick="chatbot.hasPrescription()">Yes, I have prescription</button>
                <button onclick="chatbot.noPrescription()">No, I need consultation</button>
            </div>
        `;
        this.addActionButtons(buttonsHTML);
    }

    selectQuantity(qty) {
        this.removeActionButtons();
        this.addMessage(`${qty} strip(s)`, 'user');
        
        if (this.currentMedicine) {
            const total = this.currentMedicine.price * qty;
            this.addMessage(`Perfect! Adding ${qty} x ${this.currentMedicine.name} = ₹${total} to your cart. Proceeding to checkout...`, 'bot');
            
            setTimeout(() => {
                this.addMessage('✅ Added to cart successfully! You can continue shopping or go to checkout. Need anything else?', 'bot');
                this.simulateAddToCart(this.currentMedicine.id, qty);
            }, 1500);
        }
    }

    customQuantity() {
        this.removeActionButtons();
        this.addMessage('How many strips do you need?', 'bot');
        // Set flag to expect quantity input
        this.currentAction = 'expecting_quantity';
    }

    hasPrescription() {
        this.removeActionButtons();
        this.addMessage('Yes, I have prescription', 'user');
        this.addMessage('Great! Please upload your prescription when you checkout. I\'ll add this medicine to your cart.', 'bot');
        this.showQuantityButtons();
    }

    noPrescription() {
        this.removeActionButtons();
        this.addMessage('No, I need consultation', 'user');
        this.addMessage('No problem! Our pharmacist can help you. Call +91-9876543210 for free consultation. They can also issue prescription if needed.', 'bot');
    }

    addActionButtons(html) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const buttonDiv = document.createElement('div');
        buttonDiv.className = 'bot-action-buttons';
        buttonDiv.innerHTML = html;
        messagesContainer.appendChild(buttonDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    removeActionButtons() {
        const buttons = document.querySelector('.bot-action-buttons');
        if (buttons) buttons.remove();
    }

    simulateAddToCart(medicineId, quantity) {
        // Actually add to cart via API
        fetch('auto_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'add_medicine',
                medicine_id: medicineId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Successfully added to cart');
            } else if (data.redirect) {
                this.addMessage('Please login first to add medicines to cart. I can redirect you to login page.', 'bot');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            }
        })
        .catch(error => {
            console.log('Cart integration not available, using simulation');
        });
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
let chatbot;
document.addEventListener('DOMContentLoaded', () => {
    chatbot = new MediCareChatbot();
});
