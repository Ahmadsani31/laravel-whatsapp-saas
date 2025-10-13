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
    public $overallStats = [];
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
        $this->calculateOverallStats();
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
                
            // Recalculate overall stats after loading campaigns
            $this->calculateOverallStats();
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
            $this->calculateOverallStats();
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
            $this->dispatch('campaign-started', ['name' => $campaign->name]);
            $this->loadCampaigns();
            $this->calculateOverallStats();
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
            $this->dispatch('campaign-paused', ['name' => $campaign->name]);
            $this->loadCampaigns();
            $this->calculateOverallStats();
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
            $this->dispatch('campaign-stopped', ['name' => $campaign->name]);
            $this->loadCampaigns();
            $this->calculateOverallStats();
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
            $this->calculateOverallStats();
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
            $this->calculateOverallStats();
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
            $this->calculateOverallStats();
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
            $this->dispatch('campaign-deleted', ['name' => $campaignName]);
            $this->loadCampaigns();
            $this->calculateOverallStats();
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
        // Send notification via Notyf using the new simplified system
        $this->dispatch('notify', $type, $message);
        
        // Also set local message as fallback
        $this->message = $message;
        $this->messageType_alert = $type;
    }

    public function calculateOverallStats()
    {
        try {
            $campaigns = Campaign::all();
            
            $totalRecipients = $campaigns->sum('total_recipients');
            $totalSent = $campaigns->sum('sent_count');
            $totalDelivered = $campaigns->sum('delivered_count');
            $totalRead = $campaigns->sum('read_count');
            $totalReplies = $campaigns->sum('reply_count');
            $totalFailed = $campaigns->sum('failed_count');
            
            // Calculate average rates
            $campaignsWithSent = $campaigns->where('sent_count', '>', 0);
            $avgSuccessRate = $campaignsWithSent->count() > 0 
                ? round($campaignsWithSent->avg('success_rate'), 1) 
                : 0;
            
            $campaignsWithDelivered = $campaigns->where('delivered_count', '>', 0);
            $avgReadRate = $campaignsWithDelivered->count() > 0 
                ? round($campaignsWithDelivered->avg('read_rate'), 1) 
                : 0;
            
            $avgReplyRate = $campaignsWithDelivered->count() > 0 
                ? round($campaignsWithDelivered->avg('reply_rate'), 1) 
                : 0;
            
            $activeCampaigns = $campaigns->whereIn('status', [
                Campaign::STATUS_RUNNING, 
                Campaign::STATUS_SCHEDULED
            ])->count();

            $this->overallStats = [
                'total_recipients' => $totalRecipients,
                'total_sent' => $totalSent,
                'total_delivered' => $totalDelivered,
                'total_read' => $totalRead,
                'total_replies' => $totalReplies,
                'total_failed' => $totalFailed,
                'avg_success_rate' => $avgSuccessRate,
                'avg_read_rate' => $avgReadRate,
                'avg_reply_rate' => $avgReplyRate,
                'active_campaigns' => $activeCampaigns
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate overall stats: ' . $e->getMessage());
            $this->overallStats = [
                'total_recipients' => 0,
                'total_sent' => 0,
                'total_delivered' => 0,
                'total_read' => 0,
                'total_replies' => 0,
                'total_failed' => 0,
                'avg_success_rate' => 0,
                'avg_read_rate' => 0,
                'avg_reply_rate' => 0,
                'active_campaigns' => 0
            ];
        }
    }

    public function render()
    {
        return view('livewire.campaign-manager', [
            'overallStats' => $this->overallStats
        ]);
    }
}
