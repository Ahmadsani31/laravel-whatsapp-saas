# WhatsApp Engine - Node.js

A Node.js WhatsApp integration engine using Baileys library for seamless WhatsApp Business API functionality.

## 🚀 Installation & Setup

### 1. Install Dependencies
```bash
cd whatsapp-engine
npm install
```

### 2. Environment Configuration
Create `.env` file with the following variables:
```env
WHATSAPP_ENGINE_PORT=3000
APP_URL=http://localhost:8000
WEBHOOK_URL=http://localhost:8000/webhook/whatsapp
NODE_ENV=development
```

### 3. Start Engine
```bash
npm start
```

## ✨ Supported Features

### 📤 Message Sending
- Send text messages
- Phone number validation
- Bulk message support
- Message status tracking

### 📥 Message Receiving
- Incoming message capture
- Automatic webhook notifications to Laravel
- Message status tracking (sent, delivered, read)
- Real-time message processing

### 🔗 Webhook Integration
Engine automatically sends notifications to Laravel for:
- **message_received**: New incoming messages
- **message_sent**: Message successfully sent
- **message_delivered**: Message delivered to recipient
- **message_read**: Message read by recipient

## 📊 API Endpoints

### Connection Status
```http
GET /status
```
**Response:**
```json
{
  "status": "connected|disconnected|qr",
  "qr": "data:image/png;base64,..."
}
```

### Send Message
```http
POST /send
Content-Type: application/json

{
  "number": "+1234567890",
  "message": "Hello World!"
}
```

### Check Number
```http
GET /check/:number
```
**Response:**
```json
{
  "exists": true,
  "number": "+1234567890",
  "message": "Number exists on WhatsApp"
}
```

### Get Messages
```http
GET /messages/:number?limit=20
```
**Response:**
```json
{
  "success": true,
  "number": "+1234567890",
  "messages": [...]
}
```

### Get Conversations
```http
GET /conversations?limit=20
```
**Response:**
```json
{
  "success": true,
  "conversations": [...]
}
```

### Mark as Read
```http
POST /mark-read
Content-Type: application/json

{
  "number": "+1234567890"
}
```

### Disconnect
```http
POST /disconnect
```
**Response:**
```json
{
  "success": true,
  "message": "Disconnected successfully"
}
```

## 🔌 Laravel Integration

### 1. Ensure Laravel is Running
```bash
php artisan serve
# Should be accessible at http://localhost:8000
```

### 2. Verify Webhook URL
The webhook endpoint should be accessible at:
```
http://localhost:8000/webhook/whatsapp
```

### 3. Monitor Logs
- **Node.js Console**: Webhook messages appear in terminal
- **Laravel Logs**: Check `storage/logs/laravel.log`

## 🔧 Troubleshooting

### Connection Issues with Laravel
```bash
# Test Laravel webhook endpoint
curl -X POST http://localhost:8000/webhook/whatsapp \
  -H "Content-Type: application/json" \
  -d '{"event_type":"test","data":{"message":"test"}}'

# Test Node.js engine
curl http://localhost:3000/status
```

### QR Code Issues
1. Open `http://localhost:3000/status` in browser
2. Scan QR code with your WhatsApp mobile app
3. Wait for connection status to change to "connected"

### Webhook Issues
- Check logs in both Node.js console and Laravel logs
- Verify `WEBHOOK_URL` is correct in `.env` file
- Ensure Laravel application is accessible from Node.js engine

## 🛡 Security Features

- **Request Validation**: All incoming requests validated
- **Error Handling**: Comprehensive error handling and logging
- **Rate Limiting**: Built-in protection against spam
- **Secure Headers**: Proper HTTP headers for webhook requests

## 📁 Project Structure

```
whatsapp-engine/
├── index.js              # Main application file
├── package.json          # Dependencies and scripts
├── .env                  # Environment configuration
├── .env.example          # Environment template
├── whatsapp-session/     # WhatsApp session data
└── node_modules/         # Dependencies
```

## 🔄 Webhook Events

### Message Sent
```json
{
  "event_type": "message_sent",
  "timestamp": 1697123456789,
  "data": {
    "phone_number": "+1234567890",
    "message_id": "unique_message_id",
    "message_content": "Hello World!",
    "timestamp": 1697123456789
  }
}
```

### Message Delivered
```json
{
  "event_type": "message_delivered",
  "timestamp": 1697123456789,
  "data": {
    "message_id": "unique_message_id",
    "timestamp": 1697123456789
  }
}
```

### Message Read
```json
{
  "event_type": "message_read",
  "timestamp": 1697123456789,
  "data": {
    "message_id": "unique_message_id",
    "timestamp": 1697123456789
  }
}
```

### Message Received
```json
{
  "event_type": "message_received",
  "timestamp": 1697123456789,
  "data": {
    "phone_number": "+1234567890",
    "message_id": "reply_message_id",
    "message_content": "Customer reply",
    "timestamp": 1697123456789
  }
}
```

## 🚀 Production Deployment

### Using PM2
```bash
# Install PM2 globally
npm install -g pm2

# Start engine with PM2
pm2 start index.js --name whatsapp-engine

# Save PM2 configuration
pm2 save
pm2 startup
```

### Environment Variables for Production
```env
NODE_ENV=production
WHATSAPP_ENGINE_PORT=3000
APP_URL=https://yourdomain.com
WEBHOOK_URL=https://yourdomain.com/webhook/whatsapp
```

## 📊 Monitoring

- **Real-time Status**: Monitor connection status via `/status` endpoint
- **Message Logs**: All messages logged with timestamps
- **Error Tracking**: Comprehensive error logging
- **Performance Metrics**: Built-in performance monitoring

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For issues and support:
- Check the troubleshooting section above
- Review logs for error messages
- Ensure all dependencies are properly installed
- Verify environment configuration