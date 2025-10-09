# 🚀 WhatsApp SaaS - Professional WhatsApp Management System

A modern SaaS system for WhatsApp automation using Laravel 11 + Livewire 3 with API Key authentication for AI agents and real-time updates.

## ✨ Features

-   🔗 **Quick Connect**: Link WhatsApp via QR Code
-   📱 **Number Validation**: Check if numbers exist on WhatsApp
-   💬 **Message Sending**: Send instant and reliable messages
-   🤖 **AI Agent Ready**: API Key authentication for AI agents
-   🔑 **API Key Management**: Secure key generation and management
-   ⚡ **Real-time Updates**: Socket.IO for instant status updates
-   🔒 **High Security**: Advanced error handling and validation
-   🔄 **Auto-reconnect**: Automatic reconnection and status refresh
-   📚 **MCP Protocol**: Model Context Protocol for AI integration

## 🛠️ Installation

### 1. Project Setup

```bash
# Clone the project
git clone https://github.com/hachchadi/whatsapp-saas.git
cd whatsapp-saas

# Install PHP dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure environment variables in .env:
# APP_NAME="WhatsApp SaaS"
# APP_URL=http://localhost:8000
# WHATSAPP_ENGINE_URL=http://localhost:3000
```

### 2. WhatsApp Engine Setup

```bash
cd whatsapp-engine
npm install
cd ..
```

## 🚀 Running the Application

### Quick Start

```bash
# Terminal 1: Start WhatsApp Engine
cd whatsapp-engine
node index.js

# Terminal 2: Start Laravel
php artisan serve
```

### Access the System

-   Open browser: `http://localhost:8000`
-   **Login with default credentials:**
    -   Admin: `admin@whatsapp-saas.com` / `password123`
    -   Demo: `demo@whatsapp-saas.com` / `demo123`
-   Scan QR code with your phone to connect
-   Start sending messages!

## 🔐 Authentication & Security

### Web Authentication

-   **Laravel Sanctum** for secure authentication
-   **Session-based** authentication for web interface
-   **Token-based** authentication for API access
-   **Protected routes** with middleware

### Default Users

```bash
# Create default users
php artisan db:seed --class=AdminUserSeeder

# Default credentials:
# Admin: admin@whatsapp-saas.com / password123
```

### API Authentication

```bash
# Get API token
curl -X POST {APP_URL}/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@whatsapp-saas.com","password":"password123"}'
```

## 🤖 AI Agent Integration

### API Key Management

```bash
# Generate API key for AI agents
php artisan api-key:generate "My AI Agent" --permissions=whatsapp_send,whatsapp_check,whatsapp_status

# Access API Keys management in web interface
# Go to Dashboard → Click key icon (🔑) in header
```

### MCP (Model Context Protocol) Endpoints

All endpoints require API Key authentication:

#### Get Server Info

```http
GET /api/mcp/info
X-API-Key: wapi_your_key_here
```

#### Send Message (AI Agents)

```http
POST /api/mcp/tools/call
X-API-Key: wapi_your_key_here
Content-Type: application/json

{
    "name": "whatsapp_send_message",
    "arguments": {
        "number": "+1234567890",
        "message": "Hello from AI!"
    }
}
```

#### Check Number (AI Agents)

```http
POST /api/mcp/tools/call
X-API-Key: wapi_your_key_here
Content-Type: application/json

{
    "name": "whatsapp_check_number",
    "arguments": {
        "number": "+1234567890"
    }
}
```

### Usage Examples

Check the `examples/` directory for:

-   **Python**: Complete client class
-   **Node.js**: Native implementation
-   **cURL**: Shell script examples
-   **AI Integration**: OpenAI, LangChain guides

## 📡 Web API Endpoints (Admin)

### Connection Status

```http
GET /api/whatsapp/status
Authorization: Bearer {token}
```

### Send Message (Web)

```http
POST /api/whatsapp/send
Authorization: Bearer {token}
Content-Type: application/json

{
    "number": "+1234567890",
    "message": "Hello from WhatsApp SaaS!"
}
```

## 🎨 Technical Features

### Frontend

-   ✅ Responsive Design
-   ✅ Real-time Status Updates
-   ✅ Auto-reconnection
-   ✅ Socket.IO Integration
-   ✅ Error Handling

### Backend

-   ✅ Laravel 10 + Livewire 3
-   ✅ Node.js + Socket.IO
-   ✅ Advanced Error Handling
-   ✅ Comprehensive Logging
-   ✅ High Security
-   ✅ Auto Status Refresh

## 🔧 Production Deployment

### 1. Server Setup

```bash
# Install requirements
sudo apt update
sudo apt install nginx php8.1-fpm php8.1-mysql nodejs npm composer

# Setup PM2
npm install -g pm2
```

### 2. Deploy Project

```bash
# Upload files
git clone https://github.com/hachchadi/whatsapp-saas.git
cd whatsapp-saas

# Install dependencies
composer install --optimize-autoloader --no-dev
cd whatsapp-engine && npm install && cd ..

# Setup Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run WhatsApp engine
pm2 start whatsapp-engine/index.js --name whatsapp-engine
pm2 startup
pm2 save
```

### 3. Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/whatsapp-saas/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔍 Troubleshooting

### Common Issues and Solutions

**1. QR Code not showing**

```bash
# Check WhatsApp engine
cd whatsapp-engine
node index.js

# Check connection
curl http://localhost:3000/status
```

**2. Message sending fails**

-   Ensure WhatsApp is connected
-   Verify number format (+1234567890)
-   Check error logs

**3. Connection issues**

```bash
# Restart services
pm2 restart whatsapp-engine
php artisan config:clear
```

## 📊 Monitoring and Stats

-   📈 Real-time message statistics
-   📱 Connection status monitoring
-   🔍 Detailed error logging
-   ⚡ Performance optimization

## 🤝 Contributing

We welcome contributions! Please:

1. Fork the project
2. Create a feature branch
3. Add improvements
4. Submit a Pull Request

## 📄 License

This project is licensed under the MIT License.
---
