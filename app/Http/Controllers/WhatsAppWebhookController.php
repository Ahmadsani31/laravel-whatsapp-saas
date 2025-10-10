<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CampaignService;

class WhatsAppWebhookController extends Controller
{
    protected $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    /**
     * Handle webhook from WhatsApp Engine
     */
    public function handleWebhook(Request $request)
    {
        try {
            Log::info('WhatsApp webhook received', [
                'headers' => $request->headers->all(),
                'data' => $request->all()
            ]);

            $data = $request->all();

            // Validate data from Node.js Engine
            if (!isset($data['event_type']) || !isset($data['data'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid webhook data'], 400);
            }

            $eventType = $data['event_type'];
            $eventData = $data['data'];

            // Process different events
            switch ($eventType) {
                case 'message_received':
                    $this->processIncomingMessage($eventData);
                    break;
                    
                case 'message_delivered':
                    $this->processDeliveryStatus($eventData);
                    break;
                    
                case 'message_read':
                    $this->processReadStatus($eventData);
                    break;
                    
                case 'message_sent':
                    $this->processSentMessage($eventData);
                    break;
                    
                default:
                    Log::info("Unknown webhook event type: {$eventType}");
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Process sent message (to save message_id)
     */
    protected function processSentMessage($data)
    {
        try {
            if (!isset($data['phone_number']) || !isset($data['message_id'])) {
                return;
            }

            $phoneNumber = $this->formatPhoneNumber($data['phone_number']);
            $messageId = $data['message_id'];

            // Find message in database and update message_id
            $message = \App\Models\CampaignMessage::where('phone_number', $phoneNumber)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($message) {
                $message->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'whatsapp_message_id' => $messageId
                ]);

                $this->campaignService->updateCampaignStats($message->campaign);
                Log::info("Message marked as sent: {$message->id} with WhatsApp ID: {$messageId}");
            }

        } catch (\Exception $e) {
            Log::error('Failed to process sent message: ' . $e->getMessage());
        }
    }

    /**
     * Process delivery status
     */
    protected function processDeliveryStatus($data)
    {
        try {
            if (!isset($data['message_id'])) {
                return;
            }

            $this->campaignService->processDeliveryReceipt($data['message_id']);

        } catch (\Exception $e) {
            Log::error('Failed to process delivery status: ' . $e->getMessage());
        }
    }

    /**
     * Process read status
     */
    protected function processReadStatus($data)
    {
        try {
            if (!isset($data['message_id'])) {
                return;
            }

            $this->campaignService->processReadReceipt($data['message_id']);

        } catch (\Exception $e) {
            Log::error('Failed to process read status: ' . $e->getMessage());
        }
    }

    /**
     * Process incoming message from Node.js Engine
     */
    protected function processIncomingMessage($data)
    {
        try {
            if (!isset($data['phone_number']) || !isset($data['message_content'])) {
                return;
            }

            $phoneNumber = $data['phone_number'];
            $messageContent = $data['message_content'];
            $whatsappMessageId = $data['message_id'] ?? null;

            // Process reply
            $this->campaignService->processIncomingReply(
                $phoneNumber,
                $messageContent,
                $whatsappMessageId
            );

            Log::info("Processed incoming message from: {$phoneNumber}");

        } catch (\Exception $e) {
            Log::error('Failed to process incoming message: ' . $e->getMessage());
        }
    }

    /**
     * Format phone number
     */
    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove spaces and unwanted characters
        $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);
        
        // Add country code if not present
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = '+' . $phoneNumber;
        }
        
        return $phoneNumber;
    }



    /**
     * التحقق من webhook (للتحقق الأولي من WhatsApp)
     */
    public function verifyWebhook(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        $verifyToken = config('whatsapp.webhook_verify_token', 'your_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }


}