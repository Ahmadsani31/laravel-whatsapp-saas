#!/bin/bash

# WhatsApp SaaS API - cURL Examples
# This script demonstrates how to use the WhatsApp SaaS API with cURL

# Configuration
API_KEY="wapi_7L3fjUdy8cCfTzqBL648eNA8aeBC4ww5CRns6TAaowXFrMPhnMAZL2Lr"
BASE_URL="http://localhost:8000"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== WhatsApp SaaS API - cURL Examples ===${NC}"
echo

# Function to make API calls with proper error handling
make_api_call() {
    local method=$1
    local endpoint=$2
    local data=$3
    local description=$4
    
    echo -e "${YELLOW}$description${NC}"
    echo "Endpoint: $method $endpoint"
    
    if [ "$method" = "GET" ]; then
        response=$(curl -s -w "\n%{http_code}" \
            -H "X-API-Key: $API_KEY" \
            -H "Accept: application/json" \
            "$BASE_URL$endpoint")
    else
        response=$(curl -s -w "\n%{http_code}" \
            -X "$method" \
            -H "X-API-Key: $API_KEY" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            -d "$data" \
            "$BASE_URL$endpoint")
    fi
    
    # Extract HTTP status code (last line)
    http_code=$(echo "$response" | tail -n1)
    # Extract response body (all lines except last)
    response_body=$(echo "$response" | head -n -1)
    
    if [ "$http_code" -eq 200 ] || [ "$http_code" -eq 201 ]; then
        echo -e "${GREEN}✓ Success (HTTP $http_code)${NC}"
        echo "$response_body" | jq . 2>/dev/null || echo "$response_body"
    else
        echo -e "${RED}✗ Error (HTTP $http_code)${NC}"
        echo "$response_body" | jq . 2>/dev/null || echo "$response_body"
    fi
    
    echo
    echo "---"
    echo
}

# 1. Get Server Information
make_api_call "GET" "/api/mcp/info" "" "Getting Server Information"

# 2. List Available Tools
make_api_call "GET" "/api/mcp/tools/list" "" "Listing Available Tools"

# 3. Get Connection Status
status_data='{
    "name": "whatsapp_get_status",
    "arguments": {}
}'
make_api_call "POST" "/api/mcp/tools/call" "$status_data" "Getting WhatsApp Connection Status"

# 4. Check Number Existence
check_data='{
    "name": "whatsapp_check_number",
    "arguments": {
        "number": "+1234567890"
    }
}'
make_api_call "POST" "/api/mcp/tools/call" "$check_data" "Checking Number Existence (+1234567890)"

# 5. Get Conversations
conversations_data='{
    "name": "whatsapp_get_conversations",
    "arguments": {
        "limit": 10
    }
}'
make_api_call "POST" "/api/mcp/tools/call" "$conversations_data" "Getting Recent Conversations"

# 6. Get Messages from a Number (commented out by default)
echo -e "${YELLOW}Get Messages Example (commented out):${NC}"
echo "To get messages from a specific number, uncomment and modify:"
echo
echo "messages_data='{
    \"name\": \"whatsapp_get_messages\",
    \"arguments\": {
        \"number\": \"+1234567890\",
        \"limit\": 20
    }
}'"
echo "make_api_call \"POST\" \"/api/mcp/tools/call\" \"\$messages_data\" \"Getting Messages\""
echo

# 7. Mark Messages as Read (commented out by default)
echo -e "${YELLOW}Mark as Read Example (commented out):${NC}"
echo "To mark messages as read, uncomment and modify:"
echo
echo "read_data='{
    \"name\": \"whatsapp_mark_read\",
    \"arguments\": {
        \"number\": \"+1234567890\"
    }
}'"
echo "make_api_call \"POST\" \"/api/mcp/tools/call\" \"\$read_data\" \"Marking Messages as Read\""
echo

# 8. Send Message (commented out by default)
echo -e "${YELLOW}Send Message Example (commented out):${NC}"
echo "To send a message, uncomment and modify the following:"
echo
echo "send_data='{
    \"name\": \"whatsapp_send_message\",
    \"arguments\": {
        \"number\": \"+1234567890\",
        \"message\": \"Hello from cURL!\"
    }
}'"
echo "make_api_call \"POST\" \"/api/mcp/tools/call\" \"\$send_data\" \"Sending WhatsApp Message\""
echo

# Uncomment the following lines to actually use these features:
# messages_data='{
#     "name": "whatsapp_get_messages",
#     "arguments": {
#         "number": "+1234567890",
#         "limit": 20
#     }
# }'
# make_api_call "POST" "/api/mcp/tools/call" "$messages_data" "Getting Messages"

# read_data='{
#     "name": "whatsapp_mark_read",
#     "arguments": {
#         "number": "+1234567890"
#     }
# }'
# make_api_call "POST" "/api/mcp/tools/call" "$read_data" "Marking Messages as Read"

# send_data='{
#     "name": "whatsapp_send_message",
#     "arguments": {
#         "number": "+1234567890",
#         "message": "Hello from cURL!"
#     }
# }'
# make_api_call "POST" "/api/mcp/tools/call" "$send_data" "Sending WhatsApp Message"

echo -e "${BLUE}=== Examples Complete ===${NC}"
echo
echo -e "${GREEN}Tips:${NC}"
echo "1. Replace the API_KEY with your actual key"
echo "2. Update phone numbers with real numbers"
echo "3. Uncomment the send message example to test sending"
echo "4. Check the API documentation for more endpoints"
echo
echo -e "${YELLOW}Authentication Methods:${NC}"
echo "1. X-API-Key header (recommended): -H \"X-API-Key: your_key\""
echo "2. Authorization header: -H \"Authorization: Bearer your_key\""
echo "3. Query parameter: \"?api_key=your_key\""