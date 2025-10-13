<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\CampaignReply;
use App\Models\CampaignRestart;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function createCampaign(array $data)
    {
        DB::beginTransaction();

        try {
            // Create campaign
            $campaign = Campaign::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'message_type' => $data['message_type'],
                'message_content' => $data['message_content'] ?? null,
                'template_name' => $data['template_name'] ?? null,
                'template_params' => $data['template_params'] ?? [],
                'phone_numbers' => $data['phone_numbers'],
                'status' => Campaign::STATUS_DRAFT,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'total_recipients' => count($data['phone_numbers']),
                'user_id' => auth()->id()
            ]);

            // Create campaign messages
            foreach ($data['phone_numbers'] as $phoneNumber) {
                CampaignMessage::create([
                    'campaign_id' => $campaign->id,
                    'phone_number' => $this->formatPhoneNumber($phoneNumber),
                    'message_content' => $this->prepareMessageContent($campaign, $phoneNumber),
                    'status' => CampaignMessage::STATUS_PENDING
                ]);
            }

            DB::commit();
            return $campaign;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create campaign: ' . $e->getMessage());
            throw $e;
        }
    }

    public function startCampaign(Campaign $campaign)
    {
        if (!$campaign->canStart()) {
            throw new \Exception('Campaign cannot be started in current status: ' . $campaign->status);
        }

        $campaign->update([
            'status' => Campaign::STATUS_RUNNING,
            'started_at' => now()
        ]);

        // Send messages
        $this->processCampaignMessages($campaign);

        return $campaign;
    }

    public function pauseCampaign(Campaign $campaign)
    {
        if (!$campaign->canPause()) {
            throw new \Exception('Campaign cannot be paused in current status: ' . $campaign->status);
        }

        $campaign->update(['status' => Campaign::STATUS_PAUSED]);
        return $campaign;
    }

    public function stopCampaign(Campaign $campaign)
    {
        if (!$campaign->canStop()) {
            throw new \Exception('Campaign cannot be stopped in current status: ' . $campaign->status);
        }

        $campaign->update([
            'status' => Campaign::STATUS_COMPLETED,
            'completed_at' => now()
        ]);

        return $campaign;
    }

    public function processCampaignMessages(Campaign $campaign)
    {
        $pendingMessages = $campaign->messages()
            ->where('status', CampaignMessage::STATUS_PENDING)
            ->get();

        foreach ($pendingMessages as $message) {
            $this->processSingleMessage($message);
        }

        // Update campaign status if completed
        $this->checkCampaignCompletion($campaign);
    }

    public function processSingleMessage(CampaignMessage $message)
    {
        try {
            $result = $this->whatsappService->sendMessage(
                $message->phone_number,
                $message->message_content
            );

            if ($result['success'] ?? false) {
                $message->markAsSent($result['messageId'] ?? null);
                $this->updateCampaignStats($message->campaign);
            } else {
                $message->markAsFailed($result['error'] ?? 'Unknown error');
            }

            // Small delay to avoid blocking
            usleep(500000); // 0.5 seconds

        } catch (\Exception $e) {
            Log::error('Failed to send campaign message: ' . $e->getMessage());
            $message->markAsFailed($e->getMessage());
        }
    }

    public function updateCampaignStats(Campaign $campaign)
    {
        $stats = $campaign->messages()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as read_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed
            ', [
                CampaignMessage::STATUS_SENT,
                CampaignMessage::STATUS_DELIVERED,
                CampaignMessage::STATUS_READ,
                CampaignMessage::STATUS_FAILED
            ])
            ->first();

        $replyCount = $campaign->replies()->count();

        $campaign->update([
            'sent_count' => $stats->sent,
            'delivered_count' => $stats->delivered,
            'read_count' => $stats->read_count,
            'failed_count' => $stats->failed,
            'reply_count' => $replyCount
        ]);
    }

    protected function checkCampaignCompletion(Campaign $campaign)
    {
        $pendingCount = $campaign->messages()
            ->where('status', CampaignMessage::STATUS_PENDING)
            ->count();

        if ($pendingCount === 0 && $campaign->status === Campaign::STATUS_RUNNING) {
            $campaign->update([
                'status' => Campaign::STATUS_COMPLETED,
                'completed_at' => now()
            ]);
        }
    }

    protected function prepareMessageContent(Campaign $campaign, $phoneNumber)
    {
        if ($campaign->message_type === Campaign::MESSAGE_TYPE_TEXT) {
            return $campaign->message_content;
        }

        // For templates, variable processing can be added here
        return $campaign->message_content;
    }

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

    public function parsePhoneNumbers($input)
    {
        // Split text into separate numbers
        $numbers = preg_split('/[\n,;]+/', $input);

        // Clean and format numbers
        $cleanNumbers = [];
        foreach ($numbers as $number) {
            $number = trim($number);
            if (!empty($number)) {
                $cleanNumbers[] = $this->formatPhoneNumber($number);
            }
        }

        return array_unique($cleanNumbers);
    }

    public function getCampaignStats(Campaign $campaign)
    {
        return [
            'total' => $campaign->total_recipients,
            'sent' => $campaign->sent_count,
            'delivered' => $campaign->delivered_count,
            'read' => $campaign->read_count,
            'failed' => $campaign->failed_count,
            'replies' => $campaign->reply_count,
            'pending' => $campaign->total_recipients - $campaign->sent_count - $campaign->failed_count,
            'progress_percentage' => $campaign->progress_percentage,
            'success_rate' => $campaign->success_rate,
            'read_rate' => $campaign->read_rate,
            'reply_rate' => $campaign->reply_rate
        ];
    }

    /**
     * Restart campaign
     */
    public function restartCampaign(Campaign $campaign, $reason = null)
    {
        if (!$campaign->canRestart()) {
            throw new \Exception('Campaign cannot be restarted in current status: ' . $campaign->status);
        }

        DB::beginTransaction();

        try {
            // Save restart log
            CampaignRestart::create([
                'campaign_id' => $campaign->id,
                'user_id' => auth()->id(),
                'previous_status' => $campaign->status,
                'restart_reason' => $reason,
                'restarted_at' => now()
            ]);

            // Reset all messages status to pending
            $campaign->messages()->update([
                'status' => CampaignMessage::STATUS_PENDING,
                'sent_at' => null,
                'delivered_at' => null,
                'read_at' => null,
                'failed_at' => null,
                'error_message' => null,
                'whatsapp_message_id' => null
            ]);

            // Reset campaign statistics
            $campaign->update([
                'status' => Campaign::STATUS_RUNNING,
                'started_at' => now(),
                'completed_at' => null,
                'sent_count' => 0,
                'delivered_count' => 0,
                'read_count' => 0,
                'failed_count' => 0,
                'reply_count' => 0
            ]);

            DB::commit();

            // Start sending messages
            $this->processCampaignMessages($campaign);

            return $campaign;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to restart campaign: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update campaign
     */
    public function updateCampaign(Campaign $campaign, array $data)
    {
        if (!$campaign->canEdit()) {
            throw new \Exception('Campaign cannot be edited in current status: ' . $campaign->status);
        }

        DB::beginTransaction();

        try {
            $wasRunning = $campaign->status === Campaign::STATUS_RUNNING;
            $wasCompleted = $campaign->status === Campaign::STATUS_COMPLETED;
            $wasFailed = $campaign->status === Campaign::STATUS_FAILED;

            // Pause campaign temporarily if running
            if ($wasRunning) {
                $campaign->update(['status' => Campaign::STATUS_PAUSED]);
            }

            // If campaign is completed or failed, change status to draft for editing
            if ($wasCompleted || $wasFailed) {
                $campaign->update(['status' => Campaign::STATUS_DRAFT]);
            }

            // Update campaign data
            $updateData = [];
            if (isset($data['name'])) $updateData['name'] = $data['name'];
            if (isset($data['description'])) $updateData['description'] = $data['description'];
            if (isset($data['message_content'])) $updateData['message_content'] = $data['message_content'];
            if (isset($data['template_name'])) $updateData['template_name'] = $data['template_name'];
            if (isset($data['template_params'])) $updateData['template_params'] = $data['template_params'];

            if (!empty($updateData)) {
                $campaign->update($updateData);
            }

            // Update phone numbers if changed
            if (isset($data['phone_numbers'])) {
                $this->updateCampaignPhoneNumbers($campaign, $data['phone_numbers']);
            }

            // Update message content based on campaign status
            if (isset($data['message_content'])) {
                if ($wasCompleted || $wasFailed) {
                    // For completed/failed campaigns: update all messages and reset their status
                    $campaign->messages()->update([
                        'message_content' => $data['message_content'],
                        'status' => CampaignMessage::STATUS_PENDING,
                        'sent_at' => null,
                        'delivered_at' => null,
                        'read_at' => null,
                        'failed_at' => null,
                        'error_message' => null,
                        'whatsapp_message_id' => null
                    ]);

                    // Reset campaign statistics
                    $campaign->update([
                        'sent_count' => 0,
                        'delivered_count' => 0,
                        'read_count' => 0,
                        'failed_count' => 0,
                        'reply_count' => 0,
                        'completed_at' => null
                    ]);
                } else {
                    // For other campaigns: update pending messages only
                    $campaign->messages()
                        ->where('status', CampaignMessage::STATUS_PENDING)
                        ->update(['message_content' => $data['message_content']]);
                }
            }

            // Restart campaign if it was running
            if ($wasRunning) {
                $campaign->update(['status' => Campaign::STATUS_RUNNING]);
            }

            DB::commit();
            return $campaign;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update campaign: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update phone numbers in campaign
     */
    protected function updateCampaignPhoneNumbers(Campaign $campaign, array $newPhoneNumbers)
    {
        $newPhoneNumbers = array_map([$this, 'formatPhoneNumber'], $newPhoneNumbers);
        $currentPhoneNumbers = $campaign->messages()->pluck('phone_number')->toArray();

        // For completed or failed campaigns: recreate all messages
        if (in_array($campaign->status, [Campaign::STATUS_COMPLETED, Campaign::STATUS_FAILED, Campaign::STATUS_DRAFT])) {
            // Delete all current messages
            $campaign->messages()->delete();

            // Create new messages for all numbers
            foreach ($newPhoneNumbers as $phoneNumber) {
                CampaignMessage::create([
                    'campaign_id' => $campaign->id,
                    'phone_number' => $phoneNumber,
                    'message_content' => $this->prepareMessageContent($campaign, $phoneNumber),
                    'status' => CampaignMessage::STATUS_PENDING
                ]);
            }
        } else {
            // For other campaigns: gradual update as before
            $numbersToAdd = array_diff($newPhoneNumbers, $currentPhoneNumbers);
            $numbersToRemove = array_diff($currentPhoneNumbers, $newPhoneNumbers);

            // Add messages for new numbers
            foreach ($numbersToAdd as $phoneNumber) {
                CampaignMessage::create([
                    'campaign_id' => $campaign->id,
                    'phone_number' => $phoneNumber,
                    'message_content' => $this->prepareMessageContent($campaign, $phoneNumber),
                    'status' => CampaignMessage::STATUS_PENDING
                ]);
            }

            // Delete pending messages for removed numbers
            if (!empty($numbersToRemove)) {
                $campaign->messages()
                    ->whereIn('phone_number', $numbersToRemove)
                    ->where('status', CampaignMessage::STATUS_PENDING)
                    ->delete();
            }
        }

        // Delete pending messages for removed numbers
        if (!empty($numbersToRemove)) {
            $campaign->messages()
                ->whereIn('phone_number', $numbersToRemove)
                ->where('status', CampaignMessage::STATUS_PENDING)
                ->delete();
        }

        // Update total count
        $campaign->update([
            'phone_numbers' => $newPhoneNumbers,
            'total_recipients' => count($newPhoneNumbers)
        ]);
    }

    /**
     * Process reply from WhatsApp
     */
    public function processIncomingReply($phoneNumber, $messageContent, $whatsappMessageId = null)
    {
        try {
            $formattedNumber = $this->formatPhoneNumber($phoneNumber);

            // Search for campaign linked to this number - try multiple formats
            $campaignMessage = $this->findCampaignMessageByNumber($phoneNumber);

            $campaignId = $campaignMessage ? $campaignMessage->campaign_id : null;

            // Save reply
            $reply = CampaignReply::create([
                'campaign_id' => $campaignId,
                'campaign_message_id' => $campaignMessage ? $campaignMessage->id : null,
                'phone_number' => $formattedNumber,
                'message_content' => $messageContent,
                'whatsapp_message_id' => $whatsappMessageId,
                'received_at' => now(),
                'is_processed' => false
            ]);

            // Update campaign statistics if reply is linked to campaign
            if ($campaignId) {
                $campaign = Campaign::find($campaignId);
                if ($campaign) {
                    $this->updateCampaignStats($campaign);
                }
            }

            // Process auto reply
            $this->processAutoReplyForIncomingMessage($reply);

            return $reply;
        } catch (\Exception $e) {
            Log::error('Failed to process incoming reply: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process read receipt from WhatsApp
     */
    public function processReadReceipt($whatsappMessageId)
    {
        try {
            $message = CampaignMessage::where('whatsapp_message_id', $whatsappMessageId)->first();

            if ($message && $message->status !== CampaignMessage::STATUS_READ) {
                $message->markAsRead();
                $this->updateCampaignStats($message->campaign);

                Log::info("Message marked as read: {$message->id}");
            }
        } catch (\Exception $e) {
            Log::error('Failed to process read receipt: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process delivery receipt from WhatsApp
     */
    public function processDeliveryReceipt($whatsappMessageId)
    {
        try {
            $message = CampaignMessage::where('whatsapp_message_id', $whatsappMessageId)->first();

            if ($message && $message->status === CampaignMessage::STATUS_SENT) {
                $message->markAsDelivered();
                $this->updateCampaignStats($message->campaign);

                Log::info("Message marked as delivered: {$message->id}");
            }
        } catch (\Exception $e) {
            Log::error('Failed to process delivery receipt: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find campaign message with different number formats
     */
    protected function findCampaignMessageByNumber($phoneNumber)
    {
        // List of different formats to search
        $searchNumbers = [
            $phoneNumber, // Number as is
            $this->formatPhoneNumber($phoneNumber), // With +
            '+' . $phoneNumber, // Add + at beginning
            ltrim($phoneNumber, '+'), // Remove + from beginning
            '+0' . ltrim($phoneNumber, '+212'), // Convert 212 to 0
            '+212' . ltrim($phoneNumber, '+0'), // Convert 0 to 212
        ];

        // Remove duplicates
        $searchNumbers = array_unique($searchNumbers);

        Log::info('Searching for campaign message with numbers:', $searchNumbers);

        foreach ($searchNumbers as $searchNumber) {
            $campaignMessage = CampaignMessage::where('phone_number', $searchNumber)
                ->whereIn('status', [
                    CampaignMessage::STATUS_SENT,
                    CampaignMessage::STATUS_DELIVERED,
                    CampaignMessage::STATUS_READ
                ])
                ->orderBy('sent_at', 'desc')
                ->first();

            if ($campaignMessage) {
                Log::info("Found campaign message with number: {$searchNumber} for campaign: {$campaignMessage->campaign_id}");
                return $campaignMessage;
            }
        }

        Log::warning("No campaign message found for any format of number: {$phoneNumber}");
        return null;
    }

    /**
     * Process auto reply for incoming message
     */
    protected function processAutoReplyForIncomingMessage(CampaignReply $reply)
    {
        try {
            $autoReplyService = app(AutoReplyService::class);
            $autoReplyService->processAutoReply($reply);
        } catch (\Exception $e) {
            Log::error('Failed to process auto reply: ' . $e->getMessage());
        }
    }
}
