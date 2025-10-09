<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app', ['title' => 'Message Reader'])]
class MessageReader extends Component
{
    public $conversations = [];
    public $selectedNumber = '';
    public $messages = [];
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $embedded = false;

    public function mount($embedded = false)
    {
        $this->embedded = $embedded;
        // Don't load conversations automatically to avoid errors on page load
        // User can click refresh to load them
    }

    public function loadConversations()
    {
        $this->loading = true;
        
        try {
            $whatsAppService = app(WhatsAppService::class);
            $result = $whatsAppService->getConversations(20);
            
            if ($result['success'] ?? false) {
                $this->conversations = $result['conversations'] ?? [];
                if (empty($this->conversations)) {
                    $this->setMessage('No conversations found. Make sure WhatsApp is connected and you have recent chats.', 'info');
                } else {
                    $this->setMessage('Conversations loaded successfully', 'success');
                }
            } else {
                $this->conversations = [];
                $this->setMessage($result['error'] ?? 'WhatsApp not connected or no conversations available', 'error');
            }
        } catch (\Exception $e) {
            Log::error('Failed to load conversations: ' . $e->getMessage());
            $this->conversations = [];
            $this->setMessage('Failed to connect to WhatsApp service', 'error');
        } finally {
            $this->loading = false;
        }
    }

    public function selectConversation($number)
    {
        $this->selectedNumber = $number;
        $this->loadMessages();
    }

    public function loadMessages()
    {
        if (empty($this->selectedNumber)) {
            return;
        }

        $this->loading = true;
        
        try {
            $whatsAppService = app(WhatsAppService::class);
            $result = $whatsAppService->getMessages($this->selectedNumber, 30);
            
            if ($result['success'] ?? false) {
                $this->messages = $result['messages'] ?? [];
                $this->setMessage('Messages loaded successfully', 'success');
                
                // Mark as read
                $whatsAppService->markAsRead($this->selectedNumber);
            } else {
                $this->messages = [];
                $this->setMessage($result['error'] ?? 'Failed to load messages', 'error');
            }
        } catch (\Exception $e) {
            Log::error('Failed to load messages: ' . $e->getMessage());
            $this->setMessage('Failed to load messages', 'error');
        } finally {
            $this->loading = false;
        }
    }

    public function refreshConversations()
    {
        $this->loadConversations();
    }

    public function refreshMessages()
    {
        if (!empty($this->selectedNumber)) {
            $this->loadMessages();
        }
    }

    public function clearSelection()
    {
        $this->selectedNumber = '';
        $this->messages = [];
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
        
        // Auto-clear message after 3 seconds
        $this->dispatch('message-shown');
    }

    public function render()
    {
        if ($this->embedded) {
            return view('livewire.message-reader');
        }
        
        return view('livewire.message-reader');
    }
}