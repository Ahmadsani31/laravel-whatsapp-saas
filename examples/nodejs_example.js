#!/usr/bin/env node
/**
 * WhatsApp SaaS API - Node.js Example
 * This example demonstrates how to use the WhatsApp SaaS API with Node.js
 */

const https = require('https');
const http = require('http');
const { URL } = require('url');

class WhatsAppAPI {
    /**
     * Initialize the WhatsApp API client
     * @param {string} apiKey - Your API key (starts with 'wapi_')
     * @param {string} baseUrl - Base URL of the WhatsApp SaaS server
     */
    constructor(apiKey, baseUrl = 'http://localhost:8000') {
        this.apiKey = apiKey;
        this.baseUrl = baseUrl.replace(/\/$/, '');
        this.headers = {
            'X-API-Key': apiKey,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
    }

    /**
     * Make HTTP request to the API
     * @param {string} method - HTTP method
     * @param {string} endpoint - API endpoint
     * @param {Object} data - Request data
     * @returns {Promise<Object>} API response
     */
    async _makeRequest(method, endpoint, data = null) {
        return new Promise((resolve, reject) => {
            const url = new URL(this.baseUrl + endpoint);
            const isHttps = url.protocol === 'https:';
            const httpModule = isHttps ? https : http;

            const options = {
                hostname: url.hostname,
                port: url.port || (isHttps ? 443 : 80),
                path: url.pathname + url.search,
                method: method.toUpperCase(),
                headers: this.headers
            };

            const req = httpModule.request(options, (res) => {
                let responseData = '';

                res.on('data', (chunk) => {
                    responseData += chunk;
                });

                res.on('end', () => {
                    try {
                        const jsonData = JSON.parse(responseData);
                        
                        if (res.statusCode >= 200 && res.statusCode < 300) {
                            resolve(jsonData);
                        } else {
                            reject(new Error(`HTTP ${res.statusCode}: ${jsonData.message || jsonData.error || 'Unknown error'}`));
                        }
                    } catch (e) {
                        reject(new Error(`Failed to parse response: ${responseData}`));
                    }
                });
            });

            req.on('error', (error) => {
                reject(new Error(`Request failed: ${error.message}`));
            });

            if (data && (method.toUpperCase() === 'POST' || method.toUpperCase() === 'PUT')) {
                req.write(JSON.stringify(data));
            }

            req.end();
        });
    }

    /**
     * Get server information
     * @returns {Promise<Object>} Server information
     */
    async getServerInfo() {
        return this._makeRequest('GET', '/api/mcp/info');
    }

    /**
     * List available tools
     * @returns {Promise<Object>} Available tools
     */
    async listTools() {
        return this._makeRequest('GET', '/api/mcp/tools/list');
    }

    /**
     * Send a WhatsApp message
     * @param {string} number - Phone number in international format
     * @param {string} message - Message content to send
     * @returns {Promise<Object>} Send status
     */
    async sendMessage(number, message) {
        const data = {
            name: 'whatsapp_send_message',
            arguments: {
                number: number,
                message: message
            }
        };
        return this._makeRequest('POST', '/api/mcp/tools/call', data);
    }

    /**
     * Check if a phone number exists on WhatsApp
     * @param {string} number - Phone number in international format
     * @returns {Promise<Object>} Existence status
     */
    async checkNumber(number) {
        const data = {
            name: 'whatsapp_check_number',
            arguments: {
                number: number
            }
        };
        return this._makeRequest('POST', '/api/mcp/tools/call', data);
    }

    /**
     * Get WhatsApp connection status
     * @returns {Promise<Object>} Connection status
     */
    async getStatus() {
        const data = {
            name: 'whatsapp_get_status',
            arguments: {}
        };
        return this._makeRequest('POST', '/api/mcp/tools/call', data);
    }

    /**
     * Get recent messages from a specific number
     * @param {string} number - Phone number in international format
     * @param {number} limit - Number of messages to retrieve (max 50)
     * @returns {Promise<Object>} Messages
     */
    async getMessages(number, limit = 20) {
        const data = {
            name: 'whatsapp_get_messages',
            arguments: {
                number: number,
                limit: limit
            }
        };
        return this._makeRequest('POST', '/api/mcp/tools/call', data);
    }

    /**
     * Get list of recent conversations
     * @param {number} limit - Number of conversations to retrieve (max 50)
     * @returns {Promise<Object>} Conversations
     */
    async getConversations(limit = 20) {
        const data = {
            name: 'whatsapp_get_conversations',
            arguments: {
                limit: limit
            }
        };
        return this._makeRequest('POST', '/api/mcp/tools/call', data);
    }

    /**
     * Mark messages from a number as read
     * @param {string} number - Phone number in international format
     * @returns {Promise<Object>} Read status
     */
    async markAsRead(number) {
        const data = {
            name: 'whatsapp_mark_read',
            arguments: {
                number: number
            }
        };
        return this._makeRequest('POST', '/api/mcp/tools/call', data);
    }
}

async function main() {
    // Replace with your actual API key
    const API_KEY = 'wapi_7L3fjUdy8cCfTzqBL648eNA8aeBC4ww5CRns6TAaowXFrMPhnMAZL2Lr';
    
    // Initialize the API client
    const api = new WhatsAppAPI(API_KEY);
    
    try {
        // Get server information
        console.log('=== Server Information ===');
        const serverInfo = await api.getServerInfo();
        console.log(`Server: ${serverInfo.name} v${serverInfo.version}`);
        console.log(`Description: ${serverInfo.description}`);
        console.log();
        
        // List available tools
        console.log('=== Available Tools ===');
        const tools = await api.listTools();
        tools.tools.forEach(tool => {
            console.log(`- ${tool.name}: ${tool.description}`);
        });
        console.log();
        
        // Get connection status
        console.log('=== Connection Status ===');
        const statusResponse = await api.getStatus();
        const statusContent = JSON.parse(statusResponse.content[0].text);
        console.log(`Status: ${statusContent.status}`);
        console.log(`Message: ${statusContent.message}`);
        console.log();
        
        // Example: Check if a number exists (replace with actual number)
        const testNumber = '+1234567890';
        console.log(`=== Checking Number: ${testNumber} ===`);
        const checkResponse = await api.checkNumber(testNumber);
        const checkContent = JSON.parse(checkResponse.content[0].text);
        console.log(`Number exists: ${checkContent.exists}`);
        console.log(`Message: ${checkContent.message}`);
        console.log();
        
        // Example: Get conversations
        console.log('=== Getting Recent Conversations ===');
        const conversationsResponse = await api.getConversations();
        const conversationsContent = JSON.parse(conversationsResponse.content[0].text);
        console.log(`Success: ${conversationsContent.success}`);
        console.log(`Conversations count: ${conversationsContent.count}`);
        if (conversationsContent.conversations) {
            conversationsContent.conversations.slice(0, 3).forEach(conv => {
                console.log(`- ${conv.name} (${conv.number}): ${conv.lastMessage || 'No messages'}`);
            });
        }
        console.log();

        // Example: Get messages from a number (uncomment to use)
        // console.log(`=== Getting Messages from: ${testNumber} ===`);
        // const messagesResponse = await api.getMessages(testNumber, 10);
        // const messagesContent = JSON.parse(messagesResponse.content[0].text);
        // console.log(`Success: ${messagesContent.success}`);
        // console.log(`Messages count: ${messagesContent.count}`);
        // if (messagesContent.messages) {
        //     messagesContent.messages.slice(-5).forEach(msg => {
        //         const sender = msg.fromMe ? 'You' : msg.from;
        //         console.log(`[${sender}]: ${msg.message}`);
        //     });
        // }

        // Example: Send a message (uncomment to use)
        // console.log(`=== Sending Message to: ${testNumber} ===`);
        // const sendResponse = await api.sendMessage(testNumber, 'Hello from Node.js API client!');
        // const sendContent = JSON.parse(sendResponse.content[0].text);
        // console.log(`Success: ${sendContent.success}`);
        // console.log(`Message: ${sendContent.message}`);
        
    } catch (error) {
        console.error(`Error: ${error.message}`);
    }
}

// Run the example if this file is executed directly
if (require.main === module) {
    main();
}

module.exports = WhatsAppAPI;