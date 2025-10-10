# WhatsApp Campaign Management System

A comprehensive WhatsApp messaging platform built with Laravel and Node.js, featuring advanced campaign management, real-time message tracking, and automated reply handling.

## 🚀 Features

### Campaign Management
- **Create & Edit Campaigns**: Full CRUD operations for marketing campaigns
- **Bulk Messaging**: Send messages to multiple recipients simultaneously  
- **Campaign Restart**: Restart completed campaigns with reset statistics
- **Message Templates**: Support for both text and template messages
- **Phone Number Management**: Add/remove recipients dynamically

### Real-time Tracking
- **Message Status**: Track sent, delivered, and read status
- **Reply Tracking**: Automatic capture and linking of customer replies
- **Live Statistics**: Real-time campaign analytics and metrics
- **Webhook Integration**: Seamless integration with WhatsApp Business API

### Analytics & Reporting
- **Campaign Statistics**: Delivery rates, read rates, and reply rates
- **Reply Management**: View and manage customer responses
- **Export Functionality**: Export campaign results to CSV
- **Performance Metrics**: Comprehensive campaign performance tracking

## 🛠 Tech Stack

- **Backend**: Laravel 10+ (PHP 8.1+)
- **Frontend**: Livewire, TailwindCSS, Alpine.js
- **WhatsApp Engine**: Node.js with Baileys library
- **Database**: MySQL/PostgreSQL
- **Real-time**: WebSocket integration
- **Queue System**: Laravel Queues for message processing

## 📋 Requirements

- PHP 8.1 or higher
- Composer
- Node.js 16+ and npm
- MySQL/PostgreSQL database
- WhatsApp Business account

## ⚡ Quick Start

### 1. Clone & Install
```bash
git clone <repository-url>
cd laravel-whatsapp-saas
composer install
npm install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whatsapp_saas
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Build Assets
```bash
npm run build
```

### 6. Start Services
```bash
# Terminal 1: Laravel Application
php artisan serve

# Terminal 2: WhatsApp Engine
cd whatsapp-engine
npm install
npm start
```

## 🔧 WhatsApp Engine Setup

### 1. Install Dependencies
```bash
cd whatsapp-engine
npm install
```

### 2. Configure Environment
```bash
# Create .env file in whatsapp-engine directory
WHATSAPP_ENGINE_PORT=3000
APP_URL=http://localhost:8000
WEBHOOK_URL=http://localhost:8000/webhook/whatsapp
NODE_ENV=development
```

### 3. Start Engine & Connect WhatsApp
```bash
npm start
# Visit http://localhost:3000/status
# Scan QR code with your WhatsApp
```

## 📱 Usage Guide

### Creating Campaigns
1. Navigate to **Campaigns** section
2. Click **"New Campaign"**
3. Fill in campaign details:
   - Campaign name and description
   - Message content
   - Phone numbers (one per line)
4. Click **"Create Campaign"**

### Managing Campaigns
- **Start**: Begin sending messages
- **Pause**: Temporarily stop campaign
- **Edit**: Modify content or recipients (even completed campaigns)
- **Restart**: Reset and resend completed campaigns
- **View Details**: See detailed statistics and replies

### Monitoring Replies
- **Real-time Tracking**: Replies appear automatically
- **Reply Management**: View all replies in dedicated section
- **Export Data**: Download campaign results with replies

## 🔌 API Endpoints

### Campaign Management
```bash
# Get campaign status
GET /api/campaigns/{id}

# Create campaign
POST /api/campaigns

# Update campaign
PUT /api/campaigns/{id}
```

### Message Operations
```bash
# Send single message
POST /api/messages
{
    "phone_number": "+1234567890",
    "message": "Hello World!"
}

# Get message status
GET /api/messages/{id}/status
```

### Webhook Endpoints
```bash
# WhatsApp webhook (auto-configured)
POST /webhook/whatsapp

# Webhook verification
GET /webhook/whatsapp
```

## 🔄 Webhook Integration

The system automatically handles WhatsApp webhooks for:
- **Message Sent**: Confirms message delivery to WhatsApp
- **Message Delivered**: Updates delivery status
- **Message Read**: Tracks read receipts
- **Incoming Messages**: Captures and links customer replies

## 📊 Campaign Statistics

### Available Metrics
- **Total Recipients**: Number of target recipients
- **Sent Count**: Successfully sent messages
- **Delivered Count**: Messages delivered to devices
- **Read Count**: Messages opened by recipients
- **Reply Count**: Customer responses received
- **Failed Count**: Failed message attempts

### Calculated Rates
- **Success Rate**: (Delivered / Sent) × 100
- **Read Rate**: (Read / Delivered) × 100
- **Reply Rate**: (Replies / Delivered) × 100

## 🛡 Security Features

- **CSRF Protection**: Webhook endpoints properly secured
- **Input Validation**: All user inputs validated
- **Rate Limiting**: API endpoints rate limited
- **Authentication**: User authentication required
- **Data Sanitization**: Phone numbers and content sanitized

## 🧪 Testing

### Run Laravel Tests
```bash
php artisan test
```

### Manual Testing
1. Create a test campaign
2. Send messages via WhatsApp Engine
3. Reply from your phone
4. Verify statistics update in real-time

## 🔧 Development

### Code Style
```bash
./vendor/bin/pint
```

### Frontend Development
```bash
npm run dev
```

### Database Seeding
```bash
php artisan db:seed
```

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/     # API & Webhook controllers
│   ├── Livewire/            # Frontend components
│   ├── Models/              # Database models
│   └── Services/            # Business logic
├── whatsapp-engine/         # Node.js WhatsApp integration
├── resources/views/         # Blade templates
├── database/migrations/     # Database schema
└── routes/                  # Application routes
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Issues**: Report bugs via GitHub Issues
- **Documentation**: Check the wiki for detailed guides
- **Community**: Join our Discord server for support

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com/)
- WhatsApp integration via [Baileys](https://github.com/WhiskeySockets/Baileys)
- UI components with [TailwindCSS](https://tailwindcss.com/)
- Real-time features with [Livewire](https://laravel-livewire.com/)