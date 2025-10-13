<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Services\CampaignService;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app', ['title' => 'Campaign Details'])]
class CampaignDetails extends Component
{
    use WithPagination;

    public $campaign;
    public $campaignId;
    public $filterStatus = 'all';
    public $searchPhone = '';
    public $showReplies = false;
    public $selectedMessageReplies = [];

    // Message system
    public $message = '';
    public $messageType = '';

    public function mount($id)
    {
        $this->campaignId = $id;
        $this->loadCampaign();
    }

    public function loadCampaign()
    {
        try {
            $this->campaign = Campaign::findOrFail($this->campaignId);
        } catch (\Exception $e) {
            $this->setMessage('Campaign not found.', 'error');
            return redirect()->route('campaigns');
        }
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedSearchPhone()
    {
        $this->resetPage();
    }

    public function refreshStats()
    {
        try {
            $campaignService = app(CampaignService::class);
            $campaignService->updateCampaignStats($this->campaign);
            $this->loadCampaign();

            $this->setMessage('Campaign statistics refreshed successfully!', 'success');
        } catch (\Exception $e) {
            Log::error('Failed to refresh campaign stats: ' . $e->getMessage());
            $this->setMessage('Failed to refresh statistics.', 'error');
        }
    }

    public function retryFailedMessages()
    {
        try {
            $failedMessages = $this->campaign->messages()
                ->where('status', CampaignMessage::STATUS_FAILED)
                ->get();

            if ($failedMessages->isEmpty()) {
                $this->setMessage('No failed messages to retry.', 'info');
                return;
            }

            $campaignService = app(CampaignService::class);
            $retryCount = 0;

            foreach ($failedMessages as $message) {
                // Reset message status
                $message->update([
                    'status' => CampaignMessage::STATUS_PENDING,
                    'failed_at' => null,
                    'error_message' => null,
                    'whatsapp_message_id' => null
                ]);

                // Process the message immediately
                $campaignService->processSingleMessage($message);
                $retryCount++;
            }

            // Update campaign status if it was failed
            if ($this->campaign->status === Campaign::STATUS_FAILED) {
                $this->campaign->update(['status' => Campaign::STATUS_RUNNING]);
            }

            // Update campaign statistics
            $campaignService->updateCampaignStats($this->campaign);

            $this->setMessage("Successfully retried {$retryCount} failed messages.", 'success');
            $this->dispatch('retry-completed', ['count' => $retryCount]);
            $this->loadCampaign();
        } catch (\Exception $e) {
            Log::error('Failed to retry failed messages: ' . $e->getMessage());
            $this->setMessage('Failed to retry failed messages: ' . $e->getMessage(), 'error');
        }
    }

    public function viewReplies($messageId)
    {
        try {
            $message = CampaignMessage::with('replies')->findOrFail($messageId);
            $this->selectedMessageReplies = $message->replies->map(function ($reply) {
                return [
                    'id' => $reply->id,
                    'message_content' => $reply->message_content,
                    'received_at' => $reply->received_at->format('M j, Y H:i:s'),
                    'is_processed' => $reply->is_processed
                ];
            })->toArray();

            $this->showReplies = true;
        } catch (\Exception $e) {
            Log::error('Failed to load message replies: ' . $e->getMessage());
            $this->setMessage('Failed to load replies.', 'error');
        }
    }

    public function hideReplies()
    {
        $this->showReplies = false;
        $this->selectedMessageReplies = [];
    }

    public function resendMessage($messageId)
    {
        try {
            $message = CampaignMessage::findOrFail($messageId);

            // Reset message status to pending
            $message->update([
                'status' => CampaignMessage::STATUS_PENDING,
                'sent_at' => null,
                'delivered_at' => null,
                'read_at' => null,
                'failed_at' => null,
                'error_message' => null,
                'whatsapp_message_id' => null
            ]);

            // Process the message immediately
            $campaignService = app(CampaignService::class);
            $campaignService->processSingleMessage($message);

            $this->setMessage("Message to {$message->phone_number} has been queued for resending.", 'success');
            $this->loadCampaign();
        } catch (\Exception $e) {
            Log::error('Failed to resend message: ' . $e->getMessage());
            $this->setMessage('Failed to resend message.', 'error');
        }
    }

    public function deleteMessage($messageId)
    {
        try {
            $message = CampaignMessage::with('replies')->findOrFail($messageId);
            $phoneNumber = $message->phone_number;

            // Delete related replies first
            $message->replies()->delete();

            // Delete the message
            $message->delete();

            // Update campaign statistics
            $this->campaign->decrement('total_recipients');

            // Recalculate campaign stats
            $campaignService = app(CampaignService::class);
            $campaignService->updateCampaignStats($this->campaign);

            $this->setMessage("Message to {$phoneNumber} has been deleted successfully.", 'success');
            $this->loadCampaign();
        } catch (\Exception $e) {
            Log::error('Failed to delete message: ' . $e->getMessage());
            $this->setMessage('Failed to delete message.', 'error');
        }
    }

    #[On('bulkDeleteMessages')]
    public function bulkDeleteMessages(...$params)
    {
        try {
            // Log the received parameters for debugging
            Log::info('bulkDeleteMessages called with params:', ['params' => $params]);

            // Extract messageIds from parameters
            $messageIds = [];
            if (!empty($params)) {
                // If first parameter is an array with messageIds
                if (is_array($params) && isset($params)) {
                    $messageIds = $params;
                }
            }

            if (empty($messageIds)) {
                $this->setMessage('No messages selected for deletion.', 'error');
                return;
            }

            $messages = CampaignMessage::whereIn('id', $messageIds)->get();
            $count = $messages->count();

            if ($count === 0) {
                $this->setMessage('No valid messages found for deletion.', 'error');
                return;
            }

            foreach ($messages as $message) {
                // Delete related replies first
                $message->replies()->delete();
                // Delete the message
                $message->delete();
            }

            // Update campaign statistics
            $this->campaign->decrement('total_recipients', $count);

            // Recalculate campaign stats
            $campaignService = app(CampaignService::class);
            $campaignService->updateCampaignStats($this->campaign);

            $this->setMessage("Successfully deleted {$count} messages.", 'success');
            $this->dispatch('bulk-operation', ['success' => true, 'count' => $count, 'operation' => 'deleted']);
            $this->dispatch('bulk-operation-completed');
            $this->loadCampaign();
        } catch (\Exception $e) {
            Log::error('Failed to bulk delete messages: ' . $e->getMessage());
            $this->setMessage('Failed to delete messages.', 'error');
        }
    }

    #[On('bulkResendMessages')]
    public function bulkResendMessages(...$params)
    {
        try {
            // Log the received parameters for debugging
            Log::info('bulkResendMessages called with params:', ['params' => $params]);

            // Extract messageIds from parameters
            $messageIds = [];
            if (!empty($params)) {
                // If first parameter is an array with messageIds
                if (is_array($params) && isset($params)) {
                    $messageIds = $params;
                }
            }

            if (empty($messageIds)) {
                $this->setMessage('No messages selected for resending.', 'error');
                return;
            }

            $messages = CampaignMessage::whereIn('id', $messageIds)->get();

            if ($messages->isEmpty()) {
                $this->setMessage('No valid messages found for resending.', 'error');
                return;
            }

            $campaignService = app(CampaignService::class);
            $count = 0;

            foreach ($messages as $message) {
                // Reset message status
                $message->update([
                    'status' => CampaignMessage::STATUS_PENDING,
                    'sent_at' => null,
                    'delivered_at' => null,
                    'read_at' => null,
                    'failed_at' => null,
                    'error_message' => null,
                    'whatsapp_message_id' => null
                ]);

                // Process the message immediately
                $campaignService->processSingleMessage($message);
                $count++;
            }

            $this->setMessage("Successfully queued {$count} messages for resending.", 'success');
            $this->dispatch('bulk-operation', ['success' => true, 'count' => $count, 'operation' => 'resent']);
            $this->dispatch('bulk-operation-completed');
            $this->loadCampaign();
        } catch (\Exception $e) {
            Log::error('Failed to bulk resend messages: ' . $e->getMessage());
            $this->setMessage('Failed to resend messages.', 'error');
        }
    }

    public function exportResults()
    {
        try {
            $messages = $this->campaign->messages()->with('replies')->get();

            $csvData = "Phone Number,Status,Sent At,Delivered At,Read At,Replies Count,Latest Reply,Error Message\n";

            foreach ($messages as $message) {
                $repliesCount = $message->replies->count();
                $latestReply = $message->replies->sortByDesc('received_at')->first();

                $csvData .= sprintf(
                    "%s,%s,%s,%s,%s,%d,%s,%s\n",
                    $message->phone_number,
                    $message->status,
                    $message->sent_at ? $message->sent_at->format('Y-m-d H:i:s') : '',
                    $message->delivered_at ? $message->delivered_at->format('Y-m-d H:i:s') : '',
                    $message->read_at ? $message->read_at->format('Y-m-d H:i:s') : '',
                    $repliesCount,
                    $latestReply ? $latestReply->received_at->format('Y-m-d H:i:s') : '',
                    str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $message->error_message ?? '')
                );
            }

            $filename = 'campaign_' . $this->campaign->id . '_results_' . date('Y-m-d_H-i-s') . '.csv';

            return response()->streamDownload(function () use ($csvData) {
                echo $csvData;
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to export campaign results: ' . $e->getMessage());
            $this->setMessage('Failed to export results.', 'error');
        }
    }

    public function render()
    {
        $query = $this->campaign->messages();

        // Apply filters
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if (!empty($this->searchPhone)) {
            $query->where('phone_number', 'like', '%' . $this->searchPhone . '%');
        }

        $messages = $query->with('replies')->orderBy('created_at', 'desc')->paginate(20);

        // Campaign statistics
        $stats = [
            'total' => $this->campaign->total_recipients,
            'pending' => $this->campaign->messages()->where('status', CampaignMessage::STATUS_PENDING)->count(),
            'sent' => $this->campaign->sent_count,
            'delivered' => $this->campaign->delivered_count,
            'read' => $this->campaign->read_count,
            'failed' => $this->campaign->failed_count,
            'replies' => $this->campaign->reply_count ?? 0,
            'progress_percentage' => $this->campaign->progress_percentage,
            'success_rate' => $this->campaign->success_rate,
            'read_rate' => $this->campaign->read_rate,
            'reply_rate' => $this->campaign->reply_rate ?? 0
        ];

        return view('livewire.campaign-details', [
            'messages' => $messages,
            'stats' => $stats
        ]);
    }

    public function clearMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    private function setMessage($message, $type = 'info')
    {
        // Send notification via Notyf using the new simplified system
        $this->dispatch('notify', $type, $message);

        // Also set local message as fallback
        $this->message = $message;
        $this->messageType = $type;
    }
}
