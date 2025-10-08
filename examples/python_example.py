#!/usr/bin/env python3
"""
WhatsApp SaaS API - Python Example
This example demonstrates how to use the WhatsApp SaaS API with Python
"""

import requests
import json
from typing import Dict, Any, Optional

class WhatsAppAPI:
    """WhatsApp SaaS API Client"""
    
    def __init__(self, api_key: str, base_url: str = "http://localhost:8000"):
        """
        Initialize the WhatsApp API client
        
        Args:
            api_key: Your API key (starts with 'wapi_')
            base_url: Base URL of the WhatsApp SaaS server
        """
        self.api_key = api_key
        self.base_url = base_url.rstrip('/')
        self.headers = {
            'X-API-Key': api_key,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    
    def _make_request(self, method: str, endpoint: str, data: Optional[Dict] = None) -> Dict[str, Any]:
        """Make HTTP request to the API"""
        url = f"{self.base_url}{endpoint}"
        
        try:
            if method.upper() == 'GET':
                response = requests.get(url, headers=self.headers)
            elif method.upper() == 'POST':
                response = requests.post(url, headers=self.headers, json=data)
            else:
                raise ValueError(f"Unsupported HTTP method: {method}")
            
            response.raise_for_status()
            return response.json()
            
        except requests.exceptions.RequestException as e:
            print(f"API Request failed: {e}")
            if hasattr(e, 'response') and e.response is not None:
                try:
                    error_data = e.response.json()
                    print(f"Error details: {error_data}")
                except:
                    print(f"Response text: {e.response.text}")
            raise
    
    def get_server_info(self) -> Dict[str, Any]:
        """Get server information"""
        return self._make_request('GET', '/api/mcp/info')
    
    def list_tools(self) -> Dict[str, Any]:
        """List available tools"""
        return self._make_request('GET', '/api/mcp/tools/list')
    
    def send_message(self, number: str, message: str) -> Dict[str, Any]:
        """
        Send a WhatsApp message
        
        Args:
            number: Phone number in international format (e.g., +1234567890)
            message: Message content to send
            
        Returns:
            API response with send status
        """
        data = {
            'name': 'whatsapp_send_message',
            'arguments': {
                'number': number,
                'message': message
            }
        }
        return self._make_request('POST', '/api/mcp/tools/call', data)
    
    def check_number(self, number: str) -> Dict[str, Any]:
        """
        Check if a phone number exists on WhatsApp
        
        Args:
            number: Phone number in international format
            
        Returns:
            API response with existence status
        """
        data = {
            'name': 'whatsapp_check_number',
            'arguments': {
                'number': number
            }
        }
        return self._make_request('POST', '/api/mcp/tools/call', data)
    
    def get_status(self) -> Dict[str, Any]:
        """Get WhatsApp connection status"""
        data = {
            'name': 'whatsapp_get_status',
            'arguments': {}
        }
        return self._make_request('POST', '/api/mcp/tools/call', data)
    
    def get_messages(self, number: str, limit: int = 20) -> Dict[str, Any]:
        """
        Get recent messages from a specific number
        
        Args:
            number: Phone number in international format
            limit: Number of messages to retrieve (max 50)
            
        Returns:
            API response with messages
        """
        data = {
            'name': 'whatsapp_get_messages',
            'arguments': {
                'number': number,
                'limit': limit
            }
        }
        return self._make_request('POST', '/api/mcp/tools/call', data)
    
    def get_conversations(self, limit: int = 20) -> Dict[str, Any]:
        """
        Get list of recent conversations
        
        Args:
            limit: Number of conversations to retrieve (max 50)
            
        Returns:
            API response with conversations
        """
        data = {
            'name': 'whatsapp_get_conversations',
            'arguments': {
                'limit': limit
            }
        }
        return self._make_request('POST', '/api/mcp/tools/call', data)
    
    def mark_as_read(self, number: str) -> Dict[str, Any]:
        """
        Mark messages from a number as read
        
        Args:
            number: Phone number in international format
            
        Returns:
            API response with read status
        """
        data = {
            'name': 'whatsapp_mark_read',
            'arguments': {
                'number': number
            }
        }
        return self._make_request('POST', '/api/mcp/tools/call', data)


def main():
    """Example usage"""
    
    # Replace with your actual API key
    API_KEY = "wapi_7L3fjUdy8cCfTzqBL648eNA8aeBC4ww5CRns6TAaowXFrMPhnMAZL2Lr"
    
    # Initialize the API client
    api = WhatsAppAPI(API_KEY)
    
    try:
        # Get server information
        print("=== Server Information ===")
        server_info = api.get_server_info()
        print(f"Server: {server_info['name']} v{server_info['version']}")
        print(f"Description: {server_info['description']}")
        print()
        
        # List available tools
        print("=== Available Tools ===")
        tools = api.list_tools()
        for tool in tools['tools']:
            print(f"- {tool['name']}: {tool['description']}")
        print()
        
        # Get connection status
        print("=== Connection Status ===")
        status_response = api.get_status()
        status_content = json.loads(status_response['content'][0]['text'])
        print(f"Status: {status_content['status']}")
        print(f"Message: {status_content['message']}")
        print()
        
        # Example: Check if a number exists (replace with actual number)
        test_number = "+1234567890"
        print(f"=== Checking Number: {test_number} ===")
        check_response = api.check_number(test_number)
        check_content = json.loads(check_response['content'][0]['text'])
        print(f"Number exists: {check_content['exists']}")
        print(f"Message: {check_content['message']}")
        print()
        
        # Example: Get conversations
        print("=== Getting Recent Conversations ===")
        conversations_response = api.get_conversations()
        conversations_content = json.loads(conversations_response['content'][0]['text'])
        print(f"Success: {conversations_content['success']}")
        print(f"Conversations count: {conversations_content['count']}")
        if conversations_content['conversations']:
            for conv in conversations_content['conversations'][:3]:  # Show first 3
                print(f"- {conv['name']} ({conv['number']}): {conv.get('lastMessage', 'No messages')}")
        print()

        # Example: Get messages from a number (uncomment to use)
        # print(f"=== Getting Messages from: {test_number} ===")
        # messages_response = api.get_messages(test_number, 10)
        # messages_content = json.loads(messages_response['content'][0]['text'])
        # print(f"Success: {messages_content['success']}")
        # print(f"Messages count: {messages_content['count']}")
        # if messages_content['messages']:
        #     for msg in messages_content['messages'][-5:]:  # Show last 5
        #         sender = "You" if msg['fromMe'] else msg['from']
        #         print(f"[{sender}]: {msg['message']}")

        # Example: Send a message (uncomment to use)
        # print(f"=== Sending Message to: {test_number} ===")
        # send_response = api.send_message(test_number, "Hello from Python API client!")
        # send_content = json.loads(send_response['content'][0]['text'])
        # print(f"Success: {send_content['success']}")
        # print(f"Message: {send_content['message']}")
        
    except Exception as e:
        print(f"Error: {e}")


if __name__ == "__main__":
    main()