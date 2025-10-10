<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\CampaignReply;
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
            session()->flash('error', 'Campaign not found.');
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
            
            session()->flash('success', 'Campaign statistics refreshed successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to refresh campaign stats: ' . $e->getMessage());
            session()->flash('error', 'Failed to refresh statistics.');
        }
    }

    public function retryFailedMessages()
    {
        try {
            $failedMessages = $this->campaign->messages()
                ->where('status', CampaignMessage::STATUS_FAILED)
                ->get();

            if ($failedMessages->isEmpty()) {
                session()->flash('info', 'No failed messages to retry.');
                return;
            }

            foreach ($failedMessages as $message) {
                $message->update([
                    'status' => CampaignMessage::STATUS_PENDING,
                    'failed_at' => null,
                    'error_message' => null
                ]);
            }

            // Restart campaign if it was stopped
            if ($this->campaign->status === Campaign::STATUS_FAILED) {
                $this->campaign->update(['status' => Campaign::STATUS_RUNNING]);
            }

            $campaignService = app(CampaignService::class);
            $campaignService->processCampaignMessages($this->campaign);

            session()->flash('success', "Retrying {$failedMessages->count()} failed messages.");
            $this->loadCampaign();

        } catch (\Exception $e) {
            Log::error('Failed to retry failed messages: ' . $e->getMessage());
            session()->flash('error', 'Failed to retry failed messages.');
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
            session()->flash('error', 'Failed to load replies.');
        }
    }

    public function hideReplies()
    {
        $this->showReplies = false;
        $this->selectedMessageReplies = [];
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
            session()->flash('error', 'Failed to export results.');
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
}