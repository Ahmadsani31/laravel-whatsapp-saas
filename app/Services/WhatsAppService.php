<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    private $baseUrl;
    private $timeout;

    public function __construct()
    {
        $this->baseUrl = config('whatsapp.engine.url');
        $this->timeout = config('whatsapp.engine.timeout');
    }

    public function getStatus()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/status');
            
            if ($response->successful()) {
                $data = $response->json();
                return is_array($data) ? $data : ['status' => 'error', 'error' => 'Invalid response format'];
            }
            
            return ['status' => 'error', 'error' => 'HTTP Error: ' . $response->status()];
        } catch (\Exception $e) {
            $this->logError('WhatsApp Service Error: ' . $e->getMessage());
            return ['status' => 'error', 'error' => 'Failed to connect to server: ' . $e->getMessage()];
        }
    }

    public function sendMessage($number, $message)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/send', [
                'number' => $this->formatNumber($number),
                'message' => $message,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return is_array($data) ? $data : ['success' => false, 'error' => 'Invalid response format'];
            }
            
            return ['success' => false, 'error' => 'HTTP Error: ' . $response->status()];
        } catch (\Exception $e) {
            $this->logError('Send Message Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to send message: ' . $e->getMessage()];
        }
    }

    public function checkNumber($number)
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/check/' . $this->formatNumber($number));
            
            if ($response->successful()) {
                $data = $response->json();
                return is_array($data) ? $data : ['exists' => false, 'error' => 'Invalid response format'];
            }
            
            return ['exists' => false, 'error' => 'HTTP Error: ' . $response->status()];
        } catch (\Exception $e) {
            $this->logError('Check Number Error: ' . $e->getMessage());
            return ['exists' => false, 'error' => 'Failed to check number: ' . $e->getMessage()];
        }
    }

    public function disconnect()
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/disconnect');
            
            if ($response->successful()) {
                $data = $response->json();
                return is_array($data) ? $data : ['success' => false, 'error' => 'Invalid response format'];
            }
            
            return ['success' => false, 'error' => 'HTTP Error: ' . $response->status()];
        } catch (\Exception $e) {
            $this->logError('Disconnect Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to disconnect: ' . $e->getMessage()];
        }
    }

    public function getMessages($number, $limit = 20)
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/messages/' . $this->formatNumber($number), [
                'limit' => $limit
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return is_array($data) ? $data : ['success' => false, 'error' => 'Invalid response format'];
            }
            
            return ['success' => false, 'error' => 'HTTP Error: ' . $response->status()];
        } catch (\Exception $e) {
            $this->logError('Get Messages Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to get messages: ' . $e->getMessage()];
        }
    }

    public function getConversations($limit = 20)
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/conversations', [
                'limit' => $limit
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return is_array($data) ? $data : ['success' => false, 'error' => 'Invalid response format'];
            }
            
            return ['success' => false, 'error' => 'HTTP Error: ' . $response->status()];
        } catch (\Exception $e) {
            $this->logError('Get Conversations Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to get conversations: ' . $e->getMessage()];
        }
    }

    public function markAsRead($number)
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/mark-read', [
                'number' => $this->formatNumber($number)
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return is_array($data) ? $data : ['success' => false, 'error' => 'Invalid response format'];
            }
            
            return ['success' => false, 'error' => 'HTTP Error: ' . $response->status()];
        } catch (\Exception $e) {
            $this->logError('Mark as Read Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to mark as read: ' . $e->getMessage()];
        }
    }

    public function getMessageStatus($messageId)
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/message-status/' . $messageId);
            
            if ($response->successful()) {
                $data = $response->json();
                return is_array($data) ? $data : ['success' => false, 'error' => 'Invalid response format'];
            }
            
            return ['success' => false, 'error' => 'HTTP Error: ' . $response->status()];
        } catch (\Exception $e) {
            $this->logError('Get Message Status Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to get message status: ' . $e->getMessage()];
        }
    }

    public function sendBulkMessages($messages)
    {
        try {
            $response = Http::timeout($this->timeout * 2)->post($this->baseUrl . '/send-bulk', [
                'messages' => $messages
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return is_array($data) ? $data : ['success' => false, 'error' => 'Invalid response format'];
            }
            
            return ['success' => false, 'error' => 'HTTP Error: ' . $response->status()];
        } catch (\Exception $e) {
            $this->logError('Send Bulk Messages Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to send bulk messages: ' . $e->getMessage()];
        }
    }

    private function formatNumber($number)
    {
        // Remove any non-numeric characters except +
        $number = preg_replace('/[^\d+]/', '', $number);
        
        // Add country code if missing
        if (!str_starts_with($number, '+')) {
            $number = '+' . $number;
        }
        
        return $number;
    }

    private function logError($message)
    {
        // Try to use Laravel Log if available, otherwise use error_log
        try {
            if (class_exists('\Illuminate\Support\Facades\Log')) {
                \Illuminate\Support\Facades\Log::error($message);
            } else {
                error_log($message);
            }
        } catch (\Exception $e) {
            error_log($message);
        }
    }
}
