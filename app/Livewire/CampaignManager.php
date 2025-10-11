<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app', ['title' => 'Campaign Manager'])]
class CampaignManager extends Component
{
    use WithPagination;

    public $campaigns = [];
    public $selectedCampaign = null;
    public $showCreateForm = false;
    public $showCampaignDetails = false;
    public $showEditForm = false;
    public $editingCampaign = null;

    // Form fields
    public $name = '';
    public $description = '';
    public $messageType = 'text';
    public $messageContent = '';
    public $templateName = '';
    public $phoneNumbers = '';
    public $scheduledAt = '';

    public $message = '';
    public $messageType_alert = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'messageType' => 'required|in:text,template',
        'messageContent' => 'required|string|min:1',
        'phoneNumbers' => 'required|string|min:10'
    ];

    protected $messages = [
        'name.required' => 'Campaign name is required.',
        'messageContent.required' => 'Message content is required.',
        'phoneNumbers.required' => 'At least one phone number is required.'
    ];

    public function mount()
    {
        $this->loadCampaigns();
    }

    public function loadCampaigns()
    {
        try {
            $this->campaigns = Campaign::with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($campaign) {
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name,
                        'description' => $campaign->description,
                        'status' => $campaign->status,
                        'status_color' => $campaign->status_color,
                        'total_recipients' => $campaign->total_recipients,
                        'sent_count' => $campaign->sent_count,
                        'delivered_count' => $campaign->delivered_count,
                        'read_count' => $campaign->read_count,
                        'failed_count' => $campaign->failed_count,
                        'reply_count' => $campaign->reply_count ?? 0,
                        'progress_percentage' => $campaign->progress_percentage,
                        'success_rate' => $campaign->success_rate,
                        'read_rate' => $campaign->read_rate,
                        'reply_rate' => $campaign->reply_rate ?? 0,
                        'created_at' => $campaign->created_at->format('M j, Y H:i'),
                        'started_at' => $campaign->started_at?->format('M j, Y H:i'),
                        'completed_at' => $campaign->completed_at?->format('M j, Y H:i'),
                        'can_start' => $campaign->canStart(),
                        'can_pause' => $campaign->canPause(),
                        'can_stop' => $campaign->canStop(),
                        'can_restart' => $campaign->canRestart(),
                        'can_edit' => $campaign->canEdit()
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to load campaigns: ' . $e->getMessage());
            $this->setMessage('Failed to load campaigns.', 'error');
        }
    }

    public function showCreateCampaign()
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function hideCreateForm()
    {
        $this->showCreateForm = false;
        $this->resetForm();
    }

    public function createCampaign()
    {
        $this->validate();

        try {
            $campaignService = app(CampaignService::class);

            // Parse phone numbers
            $phoneNumbers = $campaignService->parsePhoneNumbers($this->phoneNumbers);

            if (empty($phoneNumbers)) {
                $this->setMessage('No valid phone numbers found.', 'error');
                return;
            }

            $campaignData = [
                'name' => $this->name,
                'description' => $this->description,
                'message_type' => $this->messageType,
                'message_content' => $this->messageContent,
                'template_name' => $this->templateName,
                'phone_numbers' => $phoneNumbers,
                'scheduled_at' => $this->scheduledAt ? \Carbon\Carbon::parse($this->scheduledAt) : null
            ];

            $campaign = $campaignService->createCampaign($campaignData);

            $this->setMessage("Campaign '{$campaign->name}' created successfully with " . count($phoneNumbers) . " recipients!", 'success');
            $this->hideCreateForm();
            $this->loadCampaigns();
        } catch (\Exception $e) {
            Log::error('Failed to create campaign: ' . $e->getMessage());
            $this->setMessage('Failed to create campaign: ' . $e->getMessage(), 'error');
        }
    }

    public function startCampaign($campaignId)
    {
        try {
            $campaign = Campaign::findOrFail($campaignId);
            $campaignService = app(CampaignService::class);

            $campaignService->startCampaign($campaign);

            $this->setMessage("Campaign '{$campaign->name}' started successfully!", 'success');
            $this->loadCampaigns();
        } catch (\Exception $e) {
            Log::error('Failed to start campaign: ' . $e->getMessage());
            $this->setMessage('Failed to start campaign: ' . $e->getMessage(), 'error');
        }
    }

    public function pauseCampaign($campaignId)
    {
        try {
            $campaign = Campaign::findOrFail($campaignId);
            $campaignService = app(CampaignService::class);

            $campaignService->pauseCampaign($campaign);

            $this->setMessage("Campaign '{$campaign->name}' paused successfully!", 'success');
            $this->loadCampaigns();
        } catch (\Exception $e) {
            Log::error('Failed to pause campaign: ' . $e->getMessage());
            $this->setMessage('Failed to pause campaign: ' . $e->getMessage(), 'error');
        }
    }

    public function stopCampaign($campaignId)
    {
        try {
            $campaign = Campaign::findOrFail($campaignId);
            $campaignService = app(CampaignService::class);

            $campaignService->stopCampaign($campaign);

            $this->setMessage("Campaign '{$campaign->name}' stopped successfully!", 'success');
            $this->loadCampaigns();
        } catch (\Exception $e) {
            Log::error('Failed to stop campaign: ' . $e->getMessage());
            $this->setMessage('Failed to stop campaign: ' . $e->getMessage(), 'error');
        }
    }

    public function viewCampaign($campaignId)
    {
        try {
            $campaign = Campaign::with('messages')->findOrFail($campaignId);
            $this->selectedCampaign = $campaign;
            $this->showCampaignDetails = true;
        } catch (\Exception $e) {
            Log::error('Failed to load campaign details: ' . $e->getMessage());
            $this->setMessage('Failed to load campaign details.', 'error');
        }
    }

    public function hideCampaignDetails()
    {
        $this->showCampaignDetails = false;
        $this->selectedCampaign = null;
    }

    public function restartCampaign($campaignId)
    {
        try {
            $campaign = Campaign::findOrFail($campaignId);
            $campaignService = app(CampaignService::class);

            $campaignService->restartCampaign($campaign, 'Manual restart by user');

            $this->setMessage("Campaign '{$campaign->name}' restarted successfully!", 'success');
            $this->loadCampaigns();
        } catch (\Exception $e) {
            Log::error('Failed to restart campaign: ' . $e->getMessage());
            $this->setMessage('Failed to restart campaign: ' . $e->getMessage(), 'error');
        }
    }

    public function showEditCampaign($campaignId)
    {
        try {
            $campaign = Campaign::findOrFail($campaignId);

            if (!$campaign->canEdit()) {
                $this->setMessage('Campaign cannot be edited while running. Please pause it first.', 'error');
                return;
            }

            $this->editingCampaign = $campaign;
            $this->name = $campaign->name;
            $this->description = $campaign->description;
            $this->messageType = $campaign->message_type;
            $this->messageContent = $campaign->message_content;
            $this->templateName = $campaign->template_name;
            $this->phoneNumbers = implode("\n", $campaign->phone_numbers);

            $this->showEditForm = true;
        } catch (\Exception $e) {
            Log::error('Failed to load campaign for editing: ' . $e->getMessage());
            $this->setMessage('Failed to load campaign for editing.', 'error');
        }
    }

    public function hideEditForm()
    {
        $this->showEditForm = false;
        $this->editingCampaign = null;
        $this->resetForm();
    }

    public function updateCampaign()
    {
        $this->validate();

        try {
            $campaignService = app(CampaignService::class);

            // Parse phone numbers
            $phoneNumbers = $campaignService->parsePhoneNumbers($this->phoneNumbers);

            if (empty($phoneNumbers)) {
                $this->setMessage('No valid phone numbers found.', 'error');
                return;
            }

            $updateData = [
                'name' => $this->name,
                'description' => $this->description,
                'message_content' => $this->messageContent,
                'template_name' => $this->templateName,
                'phone_numbers' => $phoneNumbers
            ];

            $campaignService->updateCampaign($this->editingCampaign, $updateData);

            $this->setMessage("Campaign '{$this->editingCampaign->name}' updated successfully!", 'success');
            $this->hideEditForm();
            $this->loadCampaigns();
        } catch (\Exception $e) {
            Log::error('Failed to update campaign: ' . $e->getMessage());
            $this->setMessage('Failed to update campaign: ' . $e->getMessage(), 'error');
        }
    }

    public function cloneCampaign($campaignId)
    {
        try {
            $originalCampaign = Campaign::findOrFail($campaignId);
            $campaignService = app(CampaignService::class);

            $cloneData = [
                'name' => $originalCampaign->name . ' (Copy)',
                'description' => $originalCampaign->description,
                'message_type' => $originalCampaign->message_type,
                'message_content' => $originalCampaign->message_content,
                'template_name' => $originalCampaign->template_name,
                'phone_numbers' => $originalCampaign->phone_numbers,
                'scheduled_at' => null // Clone as draft
            ];

            $clonedCampaign = $campaignService->createCampaign($cloneData);

            $this->setMessage("Campaign '{$originalCampaign->name}' cloned successfully as '{$clonedCampaign->name}'!", 'success');
            $this->loadCampaigns();
        } catch (\Exception $e) {
            Log::error('Failed to clone campaign: ' . $e->getMessage());
            $this->setMessage('Failed to clone campaign: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteCampaign($campaignId)
    {
        try {
            $campaign = Campaign::findOrFail($campaignId);
            $campaignName = $campaign->name;

            // Delete related replies and records
            $campaign->replies()->delete();
            $campaign->restarts()->delete();

            // Delete campaign messages
            $campaign->messages()->delete();

            // Delete campaign
            $campaign->delete();

            $this->setMessage("Campaign '{$campaignName}' deleted successfully!", 'success');
            $this->loadCampaigns();
        } catch (\Exception $e) {
            Log::error('Failed to delete campaign: ' . $e->getMessage());
            $this->setMessage('Failed to delete campaign.', 'error');
        }
    }

    public function clearMessage()
    {
        $this->message = '';
        $this->messageType_alert = '';
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->messageType = 'text';
        $this->messageContent = '';
        $this->templateName = '';
        $this->phoneNumbers = '';
        $this->scheduledAt = '';
    }

    private function setMessage($message, $type = 'info')
    {
        $this->message = $message;
        $this->messageType_alert = $type;

        // Auto-clear message after 5 seconds
        $this->dispatch('message-shown');
    }

    public function render()
    {
        return view('livewire.campaign-manager');
    }
}
