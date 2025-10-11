<?php

namespace App\Services;

use App\Models\AutoReply;
use App\Models\AutoReplyLog;
use App\Models\CampaignReply;
use Illuminate\Support\Facades\Log;

class AutoReplyService
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Process auto reply for incoming message
     */
    public function processAutoReply(CampaignReply $campaignReply)
    {
        try {
            // Skip if no campaign associated
            if (!$campaignReply->campaign_id) {
                Log::info('No campaign associated with reply, skipping auto reply');
                return;
            }

            // Get active auto replies for this campaign
            $autoReplies = AutoReply::where('campaign_id', $campaignReply->campaign_id)
                ->where('is_active', true)
                ->get();

            if ($autoReplies->isEmpty()) {
                Log::info("No active auto replies found for campaign {$campaignReply->campaign_id}");
                return;
            }

            foreach ($autoReplies as $autoReply) {
                $this->sendAutoReply($autoReply, $campaignReply);
            }

        } catch (\Exception $e) {
            Log::error('Failed to process auto reply: ' . $e->getMessage());
        }
    }

    /**
     * Send auto reply if conditions are met
     */
    protected function sendAutoReply(AutoReply $autoReply, CampaignReply $campaignReply)
    {
        try {
            // Check if message matches trigger keywords
            if (!$autoReply->matchesMessage($campaignReply->message_content)) {
                Log::info("Message doesn't match keywords for auto reply {$autoReply->id}");
                return;
            }

            // Check if we should send only once per contact
            if ($autoReply->send_once_per_contact) {
                $alreadySent = AutoReplyLog::where('auto_reply_id', $autoReply->id)
                    ->where('phone_number', $campaignReply->phone_number)
                    ->where('was_successful', true)
                    ->exists();

                if ($alreadySent) {
                    Log::info("Auto reply already sent to {$campaignReply->phone_number} for auto reply {$autoReply->id}");
                    return;
                }
            }

            // Add delay if specified
            if ($autoReply->delay_seconds > 0) {
                sleep($autoReply->delay_seconds);
            }

            // Send the auto reply
            $result = $this->whatsappService->sendMessage(
                $campaignReply->phone_number,
                $autoReply->reply_message
            );

            // Log the auto reply
            $this->logAutoReply($autoReply, $campaignReply, $result);

            if ($result['success'] ?? false) {
                Log::info("Auto reply sent successfully to {$campaignReply->phone_number}");
            } else {
                Log::error("Failed to send auto reply to {$campaignReply->phone_number}: " . ($result['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error("Error sending auto reply: " . $e->getMessage());
            
            // Log the failed attempt
            $this->logAutoReply($autoReply, $campaignReply, [
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log auto reply attempt
     */
    protected function logAutoReply(AutoReply $autoReply, CampaignReply $campaignReply, array $result)
    {
        AutoReplyLog::create([
            'auto_reply_id' => $autoReply->id,
            'campaign_reply_id' => $campaignReply->id,
            'phone_number' => $campaignReply->phone_number,
            'sent_message' => $autoReply->reply_message,
            'sent_at' => now(),
            'was_successful' => $result['success'] ?? false,
            'error_message' => $result['error'] ?? null
        ]);
    }

    /**
     * Create default auto reply for campaign
     */
    public function createDefaultAutoReply($campaignId, $message = null)
    {
        $defaultMessage = $message ?? 'Thank you for your message! We have received your reply and will get back to you soon.';

        return AutoReply::create([
            'campaign_id' => $campaignId,
            'trigger_keywords' => null, // Reply to all messages
            'reply_message' => $defaultMessage,
            'is_active' => true,
            'delay_seconds' => 2, // 2 seconds delay
            'send_once_per_contact' => true
        ]);
    }

    /**
     * Get auto reply statistics for campaign
     */
    public function getAutoReplyStats($campaignId)
    {
        $autoReplies = AutoReply::where('campaign_id', $campaignId)->get();
        
        $stats = [
            'total_auto_replies' => $autoReplies->count(),
            'active_auto_replies' => $autoReplies->where('is_active', true)->count(),
            'total_sent' => 0,
            'successful_sent' => 0,
            'failed_sent' => 0
        ];

        foreach ($autoReplies as $autoReply) {
            $logs = AutoReplyLog::where('auto_reply_id', $autoReply->id)->get();
            $stats['total_sent'] += $logs->count();
            $stats['successful_sent'] += $logs->where('was_successful', true)->count();
            $stats['failed_sent'] += $logs->where('was_successful', false)->count();
        }

        return $stats;
    }
}