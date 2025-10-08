<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ApiKey;
use Illuminate\Support\Facades\Log;

class ApiKeyManager extends Component
{
    public $apiKeys = [];
    public $newKeyName = '';
    public $newKeyPermissions = '';
    public $newKeyExpiresAt = '';
    public $showNewKey = false;
    public $newApiKey = null;
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'newKeyName' => 'required|string|max:255',
        'newKeyPermissions' => 'nullable|string',
        'newKeyExpiresAt' => 'nullable|date|after:now'
    ];

    protected $messages = [
        'newKeyName.required' => 'API key name is required.',
        'newKeyName.max' => 'API key name cannot exceed 255 characters.',
        'newKeyExpiresAt.date' => 'Please enter a valid date.',
        'newKeyExpiresAt.after' => 'Expiration date must be in the future.'
    ];

    public function mount()
    {
        $this->loadApiKeys();
    }

    public function loadApiKeys()
    {
        try {
            $this->apiKeys = ApiKey::select([
                'id', 'name', 'permissions', 'is_active', 
                'last_used_at', 'expires_at', 'created_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($key) {
                $key->masked_key = $key->masked_key;
                $key->formatted_last_used = $key->last_used_at ? 
                    $key->last_used_at->diffForHumans() : 'Never';
                $key->formatted_expires = $key->expires_at ? 
                    $key->expires_at->format('M j, Y') : 'Never';
                $key->formatted_created = $key->created_at->format('M j, Y');
                return $key;
            })
            ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to load API keys: ' . $e->getMessage());
            $this->setMessage('Failed to load API keys.', 'error');
        }
    }

    public function createApiKey()
    {
        $this->validate();

        try {
            // Parse permissions
            $permissions = [];
            if (!empty($this->newKeyPermissions)) {
                $permissions = array_map('trim', explode(',', $this->newKeyPermissions));
                $permissions = array_filter($permissions);
            }

            // Parse expiration date
            $expiresAt = null;
            if (!empty($this->newKeyExpiresAt)) {
                $expiresAt = \Carbon\Carbon::parse($this->newKeyExpiresAt);
            }

            // Generate API key
            $apiKey = ApiKey::generate($this->newKeyName, $permissions, $expiresAt);

            // Show the new key (only time it will be visible)
            $this->newApiKey = [
                'name' => $apiKey->name,
                'key' => $apiKey->key,
                'permissions' => $permissions,
                'expires_at' => $expiresAt ? $expiresAt->format('M j, Y H:i') : 'Never'
            ];

            $this->showNewKey = true;

            // Reset form
            $this->reset(['newKeyName', 'newKeyPermissions', 'newKeyExpiresAt']);

            // Reload keys
            $this->loadApiKeys();

            $this->setMessage('API key created successfully!', 'success');

        } catch (\Exception $e) {
            Log::error('Failed to create API key: ' . $e->getMessage());
            $this->setMessage('Failed to create API key. Please try again.', 'error');
        }
    }

    public function toggleApiKey($keyId)
    {
        try {
            $apiKey = ApiKey::findOrFail($keyId);
            $apiKey->update(['is_active' => !$apiKey->is_active]);
            
            $this->loadApiKeys();
            
            $status = $apiKey->is_active ? 'activated' : 'deactivated';
            $this->setMessage("API key '{$apiKey->name}' has been {$status}.", 'success');

        } catch (\Exception $e) {
            Log::error('Failed to toggle API key: ' . $e->getMessage());
            $this->setMessage('Failed to update API key status.', 'error');
        }
    }

    public function deleteApiKey($keyId)
    {
        try {
            $apiKey = ApiKey::findOrFail($keyId);
            $keyName = $apiKey->name;
            $apiKey->delete();
            
            $this->loadApiKeys();
            
            $this->setMessage("API key '{$keyName}' has been deleted.", 'success');

        } catch (\Exception $e) {
            Log::error('Failed to delete API key: ' . $e->getMessage());
            $this->setMessage('Failed to delete API key.', 'error');
        }
    }

    public function dismissNewKey()
    {
        $this->showNewKey = false;
        $this->newApiKey = null;
    }

    public function clearMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    private function setMessage($message, $type = 'info')
    {
        $this->message = $message;
        $this->messageType = $type;
        
        // Auto-clear message after 5 seconds
        $this->dispatch('message-shown');
    }

    public function render()
    {
        return view('livewire.api-key-manager');
    }
}