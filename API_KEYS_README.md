# API Keys System for AI Agents

## Overview
This system provides API key-based authentication for AI agents to interact with the WhatsApp SaaS platform without requiring traditional user authentication.

## Features
- ✅ API Key generation and management
- ✅ Permission-based access control
- ✅ Expiration date support
- ✅ Usage tracking (last used timestamp)
- ✅ Enable/disable functionality
- ✅ MCP (Model Context Protocol) integration

## API Key Format
```
wapi_[56_random_characters]
```
Example: `wapi_7L3fjUdy8cCfTzqBL648eNA8aeBC4ww5CRns6TAaowXFrMPhnMAZL2Lr`

## Available Permissions
- `whatsapp_send` - Send WhatsApp messages
- `whatsapp_check` - Check if numbers exist on WhatsApp
- `whatsapp_status` - Get connection status
- `*` - All permissions (default if no permissions specified)

## Creating API Keys

### Via Command Line
```bash
# Basic API key with all permissions
php artisan api-key:generate "My AI Agent"

# API key with specific permissions
php artisan api-key:generate "Limited Agent" --permissions=whatsapp_send,whatsapp_status

# API key with expiration date
php artisan api-key:generate "Temporary Agent" --expires="2024-12-31 23:59:59"
```

### Via Web Interface
1. Login to the dashboard
2. Click the key icon (🔑) in the header
3. Fill out the form and click "Create API Key"
4. **Important**: Copy the key immediately - it won't be shown again!

### Via API (for admins)
```bash
curl -X POST http://localhost:8000/api/admin/api-keys \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My AI Agent",
    "permissions": ["whatsapp_send", "whatsapp_status"],
    "expires_at": "2024-12-31T23:59:59Z"
  }'
```

## Using API Keys

### Authentication Methods
API keys can be provided in three ways:

1. **X-API-Key Header** (Recommended)
```bash
curl -H "X-API-Key: wapi_your_key_here" http://localhost:8000/api/mcp/info
```

2. **Authorization Header**
```bash
curl -H "Authorization: Bearer wapi_your_key_here" http://localhost:8000/api/mcp/info
```

3. **Query Parameter**
```bash
curl "http://localhost:8000/api/mcp/info?api_key=wapi_your_key_here"
```

## MCP (Model Context Protocol) Endpoints

All MCP endpoints require API key authentication and are available at `/api/mcp/`:

### Server Information
```bash
GET /api/mcp/info
```

### List Available Tools
```bash
GET /api/mcp/tools/list
```

### Execute Tools
```bash
POST /api/mcp/tools/call
Content-Type: application/json

{
  "name": "whatsapp_send_message",
  "arguments": {
    "number": "+1234567890",
    "message": "Hello from AI!"
  }
}
```

### Available Tools
1. **whatsapp_send_message** - Send a message
2. **whatsapp_check_number** - Check if number exists
3. **whatsapp_get_status** - Get connection status
4. **whatsapp_get_messages** - Get recent messages from a number
5. **whatsapp_get_conversations** - Get list of recent conversations
6. **whatsapp_mark_read** - Mark messages as read

## Example Usage

### Send a WhatsApp Message
```bash
curl -X POST http://localhost:8000/api/mcp/tools/call \
  -H "X-API-Key: wapi_your_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "whatsapp_send_message",
    "arguments": {
      "number": "+1234567890",
      "message": "Hello from AI agent!"
    }
  }'
```

### Check Number Existence
```bash
curl -X POST http://localhost:8000/api/mcp/tools/call \
  -H "X-API-Key: wapi_your_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "whatsapp_check_number",
    "arguments": {
      "number": "+1234567890"
    }
  }'
```

### Get Connection Status
```bash
curl -X POST http://localhost:8000/api/mcp/tools/call \
  -H "X-API-Key: wapi_your_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "whatsapp_get_status",
    "arguments": {}
  }'
```

### Get Recent Conversations
```bash
curl -X POST http://localhost:8000/api/mcp/tools/call \
  -H "X-API-Key: wapi_your_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "whatsapp_get_conversations",
    "arguments": {
      "limit": 20
    }
  }'
```

### Get Messages from a Number
```bash
curl -X POST http://localhost:8000/api/mcp/tools/call \
  -H "X-API-Key: wapi_your_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "whatsapp_get_messages",
    "arguments": {
      "number": "+1234567890",
      "limit": 30
    }
  }'
```

### Mark Messages as Read
```bash
curl -X POST http://localhost:8000/api/mcp/tools/call \
  -H "X-API-Key: wapi_your_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "whatsapp_mark_read",
    "arguments": {
      "number": "+1234567890"
    }
  }'
```

## API Key Management

### List API Keys (Admin only)
```bash
GET /api/admin/api-keys
Authorization: Bearer YOUR_ADMIN_TOKEN
```

### Toggle API Key Status (Admin only)
```bash
PATCH /api/admin/api-keys/{id}/toggle
Authorization: Bearer YOUR_ADMIN_TOKEN
```

### Delete API Key (Admin only)
```bash
DELETE /api/admin/api-keys/{id}
Authorization: Bearer YOUR_ADMIN_TOKEN
```

## Security Best Practices

1. **Store Keys Securely**: Never commit API keys to version control
2. **Use Environment Variables**: Store keys in environment variables
3. **Rotate Keys Regularly**: Generate new keys periodically
4. **Monitor Usage**: Check the "last used" timestamp regularly
5. **Use Specific Permissions**: Don't use wildcard permissions unless necessary
6. **Set Expiration Dates**: Use temporary keys when possible

## Error Responses

### Invalid API Key
```json
{
  "error": "Invalid API key",
  "message": "The provided API key is not valid"
}
```

### Expired API Key
```json
{
  "error": "API key expired or inactive",
  "message": "The provided API key is expired or has been deactivated"
}
```

### Insufficient Permissions
```json
{
  "error": "Insufficient permissions",
  "message": "This API key does not have permission for: whatsapp_send"
}
```

## Integration with AI Frameworks

### OpenAI GPT with Function Calling
```python
import openai
import requests

def send_whatsapp_message(number, message):
    response = requests.post(
        'http://localhost:8000/api/mcp/tools/call',
        headers={
            'X-API-Key': 'wapi_your_key_here',
            'Content-Type': 'application/json'
        },
        json={
            'name': 'whatsapp_send_message',
            'arguments': {
                'number': number,
                'message': message
            }
        }
    )
    return response.json()

# Register as OpenAI function
functions = [
    {
        "name": "send_whatsapp_message",
        "description": "Send a WhatsApp message",
        "parameters": {
            "type": "object",
            "properties": {
                "number": {"type": "string"},
                "message": {"type": "string"}
            },
            "required": ["number", "message"]
        }
    }
]
```

### LangChain Integration
```python
from langchain.tools import BaseTool
import requests

class WhatsAppTool(BaseTool):
    name = "whatsapp_send"
    description = "Send WhatsApp messages"
    
    def _run(self, number: str, message: str) -> str:
        response = requests.post(
            'http://localhost:8000/api/mcp/tools/call',
            headers={'X-API-Key': 'wapi_your_key_here'},
            json={
                'name': 'whatsapp_send_message',
                'arguments': {'number': number, 'message': message}
            }
        )
        return response.json()
```

## Troubleshooting

### Common Issues

1. **401 Unauthorized**: Check if API key is correct and active
2. **403 Forbidden**: Check if API key has required permissions
3. **404 Not Found**: Verify the endpoint URL is correct
4. **500 Server Error**: Check WhatsApp engine connection

### Debug Mode
Add `?debug=1` to any MCP endpoint to get detailed error information:
```bash
curl "http://localhost:8000/api/mcp/info?debug=1" -H "X-API-Key: your_key"
```

## Environment Variables

Make sure these are set in your `.env` file:
```env
APP_URL=http://localhost:8000
WHATSAPP_ENGINE_URL=http://localhost:3000
```

## Examples

Check the `examples/` directory for practical usage examples:

- **Python**: Complete client class with error handling
- **Node.js**: Native implementation without dependencies  
- **cURL**: Shell script with all API operations
- **Integration guides**: OpenAI, LangChain, and other AI frameworks

## Support

For issues or questions:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Check WhatsApp engine logs
3. Verify API key permissions and expiration
4. Test with curl commands first before integrating with AI frameworks
5. Review examples in `examples/` directory