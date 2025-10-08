# WhatsApp SaaS API - Usage Examples

This directory contains practical examples of how to use the WhatsApp SaaS API with different programming languages and tools.

## Available Examples

### 1. Python Example (`python_example.py`)
A complete Python client class with examples of all API operations.

**Requirements:**
```bash
pip install requests
```

**Usage:**
```bash
python python_example.py
```

**Features:**
- Complete API client class
- Error handling
- Type hints
- Detailed documentation
- All API operations covered

### 2. Node.js Example (`nodejs_example.js`)
A Node.js client using only built-in modules (no external dependencies).

**Usage:**
```bash
node nodejs_example.js
```

**Features:**
- No external dependencies
- Promise-based API
- Complete error handling
- Can be used as a module

### 3. cURL Examples (`curl_examples.sh`)
Shell script with cURL commands for all API operations.

**Usage:**
```bash
chmod +x curl_examples.sh
./curl_examples.sh
```

**Features:**
- Colored output
- Error handling
- HTTP status code checking
- JSON formatting with jq (optional)

## Quick Start

1. **Get your API Key:**
   ```bash
   php artisan api-key:generate "My Test Key"
   ```

2. **Update the examples:**
   Replace the API key in each example with your actual key.

3. **Run an example:**
   Choose your preferred language and run the corresponding example.

## API Key Configuration

All examples use this API key by default (replace with yours):
```
wapi_7L3fjUdy8cCfTzqBL648eNA8aeBC4ww5CRns6TAaowXFrMPhnMAZL2Lr
```

## Available API Operations

### 1. Get Server Information
```bash
GET /api/mcp/info
```

### 2. List Available Tools
```bash
GET /api/mcp/tools/list
```

### 3. Send WhatsApp Message
```bash
POST /api/mcp/tools/call
{
  "name": "whatsapp_send_message",
  "arguments": {
    "number": "+1234567890",
    "message": "Hello World!"
  }
}
```

### 4. Check Number Existence
```bash
POST /api/mcp/tools/call
{
  "name": "whatsapp_check_number",
  "arguments": {
    "number": "+1234567890"
  }
}
```

### 5. Get Connection Status
```bash
POST /api/mcp/tools/call
{
  "name": "whatsapp_get_status",
  "arguments": {}
}
```

## Authentication Methods

The API supports three authentication methods:

### 1. X-API-Key Header (Recommended)
```bash
curl -H "X-API-Key: wapi_your_key_here" http://localhost:8000/api/mcp/info
```

### 2. Authorization Header
```bash
curl -H "Authorization: Bearer wapi_your_key_here" http://localhost:8000/api/mcp/info
```

### 3. Query Parameter
```bash
curl "http://localhost:8000/api/mcp/info?api_key=wapi_your_key_here"
```

## Error Handling

All examples include proper error handling for common scenarios:

- **401 Unauthorized**: Invalid or missing API key
- **403 Forbidden**: Insufficient permissions
- **404 Not Found**: Invalid endpoint
- **500 Server Error**: WhatsApp engine connection issues

## Integration with AI Frameworks

### OpenAI Function Calling
```python
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

### LangChain Tool
```python
from langchain.tools import BaseTool

class WhatsAppTool(BaseTool):
    name = "whatsapp_send"
    description = "Send WhatsApp messages"
    
    def _run(self, number: str, message: str) -> str:
        # Use the WhatsAppAPI class from python_example.py
        api = WhatsAppAPI("your_api_key")
        return api.send_message(number, message)
```

## Testing

Before using in production:

1. **Test with a valid phone number**
2. **Verify WhatsApp connection status**
3. **Check API key permissions**
4. **Monitor rate limits**

## Troubleshooting

### Common Issues

1. **Connection Refused**: Make sure the Laravel server is running
2. **Invalid API Key**: Check if the key is correct and active
3. **WhatsApp Not Connected**: Scan QR code in the dashboard first
4. **Permission Denied**: Verify API key has required permissions

### Debug Mode

Add `?debug=1` to any endpoint for detailed error information:
```bash
curl "http://localhost:8000/api/mcp/info?debug=1&api_key=your_key"
```

## Production Considerations

1. **Use HTTPS** in production
2. **Store API keys securely** (environment variables)
3. **Implement rate limiting** on your side
4. **Monitor API usage** and errors
5. **Set up proper logging**
6. **Use connection pooling** for high-volume applications

## Support

For issues or questions:
1. Check the main API documentation: `../API_KEYS_README.md`
2. Review Laravel logs: `storage/logs/laravel.log`
3. Test with cURL first before using in applications
4. Verify WhatsApp engine is running and connected