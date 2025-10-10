<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Campaign;
use App\Models\CampaignReply;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app', ['title' => 'Campaign Replies'])]
class CampaignReplies extends Component
{
    use WithPagination;

    public $campaign;
    public $campaignId;
    public $filterProcessed = 'all';
    public $searchPhone = '';
    public $searchContent = '';

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

    public function updatedFilterProcessed()
    {
        $this->resetPage();
    }

    public function updatedSearchPhone()
    {
        $this->resetPage();
    }

    public function updatedSearchContent()
    {
        $this->resetPage();
    }

    public function markAsProcessed($replyId)
    {
        try {
            $reply = CampaignReply::findOrFail($replyId);
            $reply->markAsProcessed();
            
            session()->flash('success', 'Reply marked as processed.');
        } catch (\Exception $e) {
            Log::error('Failed to mark reply as processed: ' . $e->getMessage());
            session()->flash('error', 'Failed to mark reply as processed.');
        }
    }

    public function markAllAsProcessed()
    {
        try {
            $this->campaign->replies()
                ->where('is_processed', false)
                ->update(['is_processed' => true]);
            
            session()->flash('success', 'All replies marked as processed.');
        } catch (\Exception $e) {
            Log::error('Failed to mark all replies as processed: ' . $e->getMessage());
            session()->flash('error', 'Failed to mark all replies as processed.');
        }
    }

    public function exportReplies()
    {
        try {
            $replies = $this->campaign->replies()->with('campaignMessage')->get();
            
            $csvData = "Phone Number,Message Content,Received At,Is Processed,Original Message\n";
            
            foreach ($replies as $reply) {
                $originalMessage = $reply->campaignMessage ? 
                    substr($reply->campaignMessage->message_content, 0, 50) . '...' : 
                    'N/A';
                
                $csvData .= sprintf(
                    "%s,%s,%s,%s,%s\n",
                    $reply->phone_number,
                    str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $reply->message_content),
                    $reply->received_at->format('Y-m-d H:i:s'),
                    $reply->is_processed ? 'Yes' : 'No',
                    str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $originalMessage)
                );
            }

            $filename = 'campaign_' . $this->campaign->id . '_replies_' . date('Y-m-d_H-i-s') . '.csv';
            
            return response()->streamDownload(function () use ($csvData) {
                echo $csvData;
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to export campaign replies: ' . $e->getMessage());
            session()->flash('error', 'Failed to export replies.');
        }
    }

    public function render()
    {
        $query = $this->campaign->replies()->with('campaignMessage');

        // Apply filters
        if ($this->filterProcessed !== 'all') {
            $isProcessed = $this->filterProcessed === 'processed';
            $query->where('is_processed', $isProcessed);
        }

        if (!empty($this->searchPhone)) {
            $query->where('phone_number', 'like', '%' . $this->searchPhone . '%');
        }

        if (!empty($this->searchContent)) {
            $query->where('message_content', 'like', '%' . $this->searchContent . '%');
        }

        $replies = $query->orderBy('received_at', 'desc')->paginate(20);

        // Reply statistics
        $stats = [
            'total' => $this->campaign->replies()->count(),
            'processed' => $this->campaign->replies()->where('is_processed', true)->count(),
            'unprocessed' => $this->campaign->replies()->where('is_processed', false)->count(),
        ];

        return view('livewire.campaign-replies', [
            'replies' => $replies,
            'stats' => $stats
        ]);
    }
}