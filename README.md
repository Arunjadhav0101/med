# MediCare E-Pharmacy

A comprehensive online pharmacy platform with AI-powered chatbot assistance.

## Features

### Core Functionality
- Medicine catalog browsing
- User registration and authentication
- Shopping cart and order management
- Admin dashboard for inventory management
- Prescription upload and verification

### New Chatbot Feature
- **24/7 AI Assistant**: Instant help for medicine queries, orders, and general information
- **Smart Responses**: Context-aware responses for common pharmacy questions
- **Mobile Responsive**: Works seamlessly on all devices
- **Easy Integration**: Floating chat button on all main pages

## Chatbot Capabilities

The MediCare chatbot can help with:
- Medicine information and availability
- Order status and delivery information
- Prescription requirements
- Payment methods and policies
- Store hours and contact information
- General pharmacy guidance

## Setup Instructions

1. **Database Setup**:
   - Import `medicare (3).sql` into your MySQL database
   - Update database credentials in `config.php`

2. **Web Server**:
   - Place files in your web server directory
   - Ensure PHP is enabled
   - Start your web server (Apache/Nginx)

3. **Chatbot**:
   - The chatbot is automatically included on main pages
   - No additional setup required
   - Backend powered by `chatbot.php`

## File Structure

```
├── home.html              # Landing page
├── catlog.php            # Medicine catalog
├── cart.php              # Shopping cart
├── orders.php            # Order management
├── chatbot.php           # Chatbot backend
├── chatbot.css           # Chatbot styling
├── chatbot.js            # Chatbot frontend
├── config.php            # Database configuration
├── styles.css            # Main stylesheet
└── medicare (3).sql      # Database schema
```

## Usage

1. Open `home.html` in your browser
2. Browse medicines in the catalog
3. Click the chat button (💬) in the bottom-right corner
4. Ask questions about medicines, orders, or general information
5. The chatbot provides instant, helpful responses

## Technical Details

- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Chatbot**: Custom PHP-based response system
- **Responsive Design**: Mobile-first approach

## Future Enhancements

- Integration with external medicine APIs
- Advanced NLP for better query understanding
- Voice chat capabilities
- Multi-language support
- Integration with prescription verification systems
