<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Campaign;
use App\Models\AutoReply;
use App\Services\AutoReplyService;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app', ['title' => 'Auto Reply Manager'])]
class AutoReplyManager extends Component
{
    public $campaign;
    public $campaignId;
    public $autoReplies = [];
    public $showCreateForm = false;
    public $editingAutoReply = null;

    // Form fields
    public $triggerKeywords = '';
    public $replyMessage = '';
    public $isActive = true;
    public $delaySeconds = 2;
    public $sendOncePerContact = true;

    public $message = '';
    public $messageType = '';

    protected $rules = [
        'replyMessage' => 'required|string|min:1|max:1000',
        'delaySeconds' => 'integer|min:0|max:300',
        'triggerKeywords' => 'nullable|string|max:500'
    ];

    public function mount($id)
    {
        $this->campaignId = $id;
        $this->loadCampaign();
        $this->loadAutoReplies();
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

    public function loadAutoReplies()
    {
        $this->autoReplies = AutoReply::where('campaign_id', $this->campaignId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function showCreateAutoReply()
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function hideCreateForm()
    {
        $this->showCreateForm = false;
        $this->editingAutoReply = null;
        $this->resetForm();
    }

    public function createAutoReply()
    {
        $this->validate();

        try {
            AutoReply::create([
                'campaign_id' => $this->campaignId,
                'trigger_keywords' => $this->triggerKeywords ?: null,
                'reply_message' => $this->replyMessage,
                'is_active' => $this->isActive,
                'delay_seconds' => $this->delaySeconds,
                'send_once_per_contact' => $this->sendOncePerContact
            ]);

            $this->setMessage('Auto reply created successfully!', 'success');
            $this->hideCreateForm();
            $this->loadAutoReplies();

        } catch (\Exception $e) {
            Log::error('Failed to create auto reply: ' . $e->getMessage());
            $this->setMessage('Failed to create auto reply.', 'error');
        }
    }

    public function editAutoReply($autoReplyId)
    {
        try {
            $autoReply = AutoReply::findOrFail($autoReplyId);
            
            $this->editingAutoReply = $autoReply;
            $this->triggerKeywords = $autoReply->trigger_keywords;
            $this->replyMessage = $autoReply->reply_message;
            $this->isActive = $autoReply->is_active;
            $this->delaySeconds = $autoReply->delay_seconds;
            $this->sendOncePerContact = $autoReply->send_once_per_contact;
            
            $this->showCreateForm = true;

        } catch (\Exception $e) {
            Log::error('Failed to load auto reply for editing: ' . $e->getMessage());
            $this->setMessage('Failed to load auto reply.', 'error');
        }
    }

    public function updateAutoReply()
    {
        $this->validate();

        try {
            $this->editingAutoReply->update([
                'trigger_keywords' => $this->triggerKeywords ?: null,
                'reply_message' => $this->replyMessage,
                'is_active' => $this->isActive,
                'delay_seconds' => $this->delaySeconds,
                'send_once_per_contact' => $this->sendOncePerContact
            ]);

            $this->setMessage('Auto reply updated successfully!', 'success');
            $this->hideCreateForm();
            $this->loadAutoReplies();

        } catch (\Exception $e) {
            Log::error('Failed to update auto reply: ' . $e->getMessage());
            $this->setMessage('Failed to update auto reply.', 'error');
        }
    }

    public function toggleAutoReply($autoReplyId)
    {
        try {
            $autoReply = AutoReply::findOrFail($autoReplyId);
            $autoReply->update(['is_active' => !$autoReply->is_active]);
            
            $status = $autoReply->is_active ? 'activated' : 'deactivated';
            $this->setMessage("Auto reply {$status} successfully!", 'success');
            $this->loadAutoReplies();

        } catch (\Exception $e) {
            Log::error('Failed to toggle auto reply: ' . $e->getMessage());
            $this->setMessage('Failed to toggle auto reply.', 'error');
        }
    }

    public function deleteAutoReply($autoReplyId)
    {
        try {
            $autoReply = AutoReply::findOrFail($autoReplyId);
            $autoReply->delete();
            
            $this->setMessage('Auto reply deleted successfully!', 'success');
            $this->loadAutoReplies();

        } catch (\Exception $e) {
            Log::error('Failed to delete auto reply: ' . $e->getMessage());
            $this->setMessage('Failed to delete auto reply.', 'error');
        }
    }

    public function createDefaultAutoReply()
    {
        try {
            $autoReplyService = app(AutoReplyService::class);
            $autoReplyService->createDefaultAutoReply($this->campaignId);
            
            $this->setMessage('Default auto reply created successfully!', 'success');
            $this->loadAutoReplies();

        } catch (\Exception $e) {
            Log::error('Failed to create default auto reply: ' . $e->getMessage());
            $this->setMessage('Failed to create default auto reply.', 'error');
        }
    }

    public function clearMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    private function resetForm()
    {
        $this->triggerKeywords = '';
        $this->replyMessage = '';
        $this->isActive = true;
        $this->delaySeconds = 2;
        $this->sendOncePerContact = true;
    }

    private function setMessage($message, $type = 'info')
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function render()
    {
        $autoReplyService = app(AutoReplyService::class);
        $stats = $autoReplyService->getAutoReplyStats($this->campaignId);

        return view('livewire.auto-reply-manager', [
            'stats' => $stats
        ]);
    }
}
