<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MCPController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Get MCP server information
     */
    public function getServerInfo()
    {
        return response()->json([
            'name' => env('APP_NAME', 'WhatsApp SaaS') . ' MCP Server',
            'version' => '1.0.0',
            'description' => 'MCP server for WhatsApp automation and messaging',
            'base_url' => env('APP_URL', 'http://localhost:8000'),
            'whatsapp_engine_url' => env('WHATSAPP_ENGINE_URL', 'http://localhost:3000'),
            'capabilities' => [
                'tools' => [
                    'whatsapp_send_message',
                    'whatsapp_check_number',
                    'whatsapp_get_status',
                    'whatsapp_get_messages',
                    'whatsapp_get_conversations',
                    'whatsapp_mark_read'
                ],
                'resources' => [
                    'connection_status',
                    'message_history'
                ]
            ],
            'author' => 'WhatsApp SaaS Team',
            'license' => 'MIT'
        ]);
    }

    /**
     * List available tools
     */
    public function listTools()
    {
        return response()->json([
            'tools' => [
                [
                    'name' => 'whatsapp_send_message',
                    'description' => 'Send a WhatsApp message to a specific number',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'number' => [
                                'type' => 'string',
                                'description' => 'Phone number in international format (e.g., +1234567890)'
                            ],
                            'message' => [
                                'type' => 'string',
                                'description' => 'Message content to send'
                            ]
                        ],
                        'required' => ['number', 'message']
                    ]
                ],
                [
                    'name' => 'whatsapp_check_number',
                    'description' => 'Check if a phone number exists on WhatsApp',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'number' => [
                                'type' => 'string',
                                'description' => 'Phone number in international format (e.g., +1234567890)'
                            ]
                        ],
                        'required' => ['number']
                    ]
                ],
                [
                    'name' => 'whatsapp_get_status',
                    'description' => 'Get current WhatsApp connection status',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => []
                    ]
                ],
                [
                    'name' => 'whatsapp_get_messages',
                    'description' => 'Get recent messages from a specific WhatsApp number',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'number' => [
                                'type' => 'string',
                                'description' => 'Phone number in international format (e.g., +1234567890)'
                            ],
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'Number of messages to retrieve (default: 20, max: 50)',
                                'minimum' => 1,
                                'maximum' => 50
                            ]
                        ],
                        'required' => ['number']
                    ]
                ],
                [
                    'name' => 'whatsapp_get_conversations',
                    'description' => 'Get list of recent WhatsApp conversations',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'Number of conversations to retrieve (default: 20, max: 50)',
                                'minimum' => 1,
                                'maximum' => 50
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'whatsapp_mark_read',
                    'description' => 'Mark messages from a specific number as read',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'number' => [
                                'type' => 'string',
                                'description' => 'Phone number in international format (e.g., +1234567890)'
                            ]
                        ],
                        'required' => ['number']
                    ]
                ]
            ]
        ]);
    }

    /**
     * Execute a tool
     */
    public function callTool(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'arguments' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid request',
                'details' => $validator->errors()
            ], 400);
        }

        $toolName = $request->input('name');
        $arguments = $request->input('arguments', []);

        Log::info('MCP Tool called', ['tool' => $toolName, 'arguments' => $arguments]);

        try {
            switch ($toolName) {
                case 'whatsapp_send_message':
                    return $this->sendMessage($arguments);

                case 'whatsapp_check_number':
                    return $this->checkNumber($arguments);

                case 'whatsapp_get_status':
                    return $this->getStatus();

                case 'whatsapp_get_messages':
                    return $this->getMessages($arguments);

                case 'whatsapp_get_conversations':
                    return $this->getConversations($arguments);

                case 'whatsapp_mark_read':
                    return $this->markAsRead($arguments);

                default:
                    return response()->json([
                        'error' => 'Unknown tool',
                        'message' => "Tool '{$toolName}' is not available"
                    ], 404);
            }
        } catch (\Exception $e) {
            Log::error('MCP Tool execution failed', [
                'tool' => $toolName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Tool execution failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send WhatsApp message
     */
    private function sendMessage(array $arguments)
    {
        $validator = Validator::make($arguments, [
            'number' => 'required|string|min:10',
            'message' => 'required|string|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid arguments',
                'details' => $validator->errors()
            ], 400);
        }

        $result = $this->whatsAppService->sendMessage(
            $arguments['number'],
            $arguments['message']
        );

        return response()->json([
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'success' => $result['success'] ?? false,
                        'message' => $result['message'] ?? $result['error'] ?? 'Unknown result',
                        'number' => $arguments['number'],
                        'timestamp' => now()->toISOString()
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ]);
    }

    /**
     * Check WhatsApp number
     */
    private function checkNumber(array $arguments)
    {
        $validator = Validator::make($arguments, [
            'number' => 'required|string|min:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid arguments',
                'details' => $validator->errors()
            ], 400);
        }

        $result = $this->whatsAppService->checkNumber($arguments['number']);

        return response()->json([
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'number' => $arguments['number'],
                        'exists' => $result['exists'] ?? false,
                        'message' => $result['message'] ?? $result['error'] ?? 'Unknown result',
                        'timestamp' => now()->toISOString()
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ]);
    }

    /**
     * Get WhatsApp status
     */
    private function getStatus()
    {
        $result = $this->whatsAppService->getStatus();

        return response()->json([
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'status' => $result['status'] ?? 'unknown',
                        'has_qr' => !empty($result['qr']),
                        'timestamp' => now()->toISOString(),
                        'message' => $this->getStatusMessage($result['status'] ?? 'unknown')
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ]);
    }

    /**
     * Get messages from a specific number
     */
    private function getMessages(array $arguments)
    {
        $validator = Validator::make($arguments, [
            'number' => 'required|string|min:10',
            'limit' => 'sometimes|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid arguments',
                'details' => $validator->errors()
            ], 400);
        }

        $limit = $arguments['limit'] ?? 20;
        $result = $this->whatsAppService->getMessages($arguments['number'], $limit);

        return response()->json([
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'success' => $result['success'] ?? false,
                        'number' => $arguments['number'],
                        'messages' => $result['messages'] ?? [],
                        'count' => count($result['messages'] ?? []),
                        'timestamp' => now()->toISOString(),
                        'message' => $result['success'] ?? false ? 'Messages retrieved successfully' : ($result['error'] ?? 'Failed to get messages')
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ]);
    }

    /**
     * Get conversations list
     */
    private function getConversations(array $arguments)
    {
        $validator = Validator::make($arguments, [
            'limit' => 'sometimes|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid arguments',
                'details' => $validator->errors()
            ], 400);
        }

        $limit = $arguments['limit'] ?? 20;
        $result = $this->whatsAppService->getConversations($limit);

        return response()->json([
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'success' => $result['success'] ?? false,
                        'conversations' => $result['conversations'] ?? [],
                        'count' => count($result['conversations'] ?? []),
                        'timestamp' => now()->toISOString(),
                        'message' => $result['success'] ?? false ? 'Conversations retrieved successfully' : ($result['error'] ?? 'Failed to get conversations')
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ]);
    }

    /**
     * Mark messages as read
     */
    private function markAsRead(array $arguments)
    {
        $validator = Validator::make($arguments, [
            'number' => 'required|string|min:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid arguments',
                'details' => $validator->errors()
            ], 400);
        }

        $result = $this->whatsAppService->markAsRead($arguments['number']);

        return response()->json([
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'success' => $result['success'] ?? false,
                        'number' => $arguments['number'],
                        'timestamp' => now()->toISOString(),
                        'message' => $result['message'] ?? $result['error'] ?? 'Unknown result'
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ]);
    }



    /**
     * Get human-readable status message
     */
    private function getStatusMessage(string $status): string
    {
        return match ($status) {
            'connected' => 'WhatsApp is connected and ready to send messages',
            'qr' => 'Waiting for QR code scan to connect WhatsApp',
            'disconnected' => 'WhatsApp is disconnected',
            'error' => 'Error connecting to WhatsApp service',
            default => 'Unknown status'
        };
    }

    /**
     * List available resources
     */
    public function listResources()
    {
        return response()->json([
            'resources' => [
                [
                    'uri' => 'whatsapp://connection-status',
                    'name' => 'WhatsApp Connection Status',
                    'description' => 'Current connection status and QR code availability',
                    'mimeType' => 'application/json'
                ],
                [
                    'uri' => 'whatsapp://capabilities',
                    'name' => 'WhatsApp Capabilities',
                    'description' => 'Available WhatsApp operations and their status',
                    'mimeType' => 'application/json'
                ]
            ]
        ]);
    }

    /**
     * Get a specific resource
     */
    public function getResource(Request $request)
    {
        $uri = $request->input('uri');

        switch ($uri) {
            case 'whatsapp://connection-status':
                $status = $this->whatsAppService->getStatus();
                return response()->json([
                    'contents' => [
                        [
                            'uri' => $uri,
                            'mimeType' => 'application/json',
                            'text' => json_encode([
                                'status' => $status['status'] ?? 'unknown',
                                'has_qr' => !empty($status['qr']),
                                'message' => $this->getStatusMessage($status['status'] ?? 'unknown'),
                                'timestamp' => now()->toISOString()
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ]);

            case 'whatsapp://capabilities':
                return response()->json([
                    'contents' => [
                        [
                            'uri' => $uri,
                            'mimeType' => 'application/json',
                            'text' => json_encode([
                                'send_message' => true,
                                'check_number' => true,
                                'get_status' => true,
                                'get_messages' => true,
                                'get_conversations' => true,
                                'mark_read' => true,
                                'qr_code_support' => true,
                                'real_time_status' => true,
                                'message_reading' => true
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ]);

            default:
                return response()->json([
                    'error' => 'Resource not found',
                    'message' => "Resource '{$uri}' is not available"
                ], 404);
        }
    }
}
