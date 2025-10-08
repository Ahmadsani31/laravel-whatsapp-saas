<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WhatsAppService;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class WhatsappManager extends Component
{
    public $number = '';
    public $message = '';
    public $checkResult = null;
    public $sendResult = null;
    public $loading = false;

    public function checkNumber()
    {
        $this->validate(['number' => 'required|string']);
        
        $this->loading = true;
        $this->checkResult = null;
        
        try {
            $service = new WhatsAppService();
            $response = $service->checkNumber($this->number);
            
            // التأكد من أن الاستجابة array
            if (!is_array($response)) {
                $response = ['error' => 'Invalid response from server'];
            }
            
            $this->checkResult = $response;
            Log::info('Number checked', ['number' => $this->number, 'result' => $response]);
        } catch (\Exception $e) {
            Log::error('Error checking number: ' . $e->getMessage());
            $this->checkResult = ['error' => 'Error checking number: ' . $e->getMessage()];
        }
        
        $this->loading = false;
    }

    public function sendMessage()
    {
        $this->validate([
            'number' => 'required|string|min:10',
            'message' => 'required|string|min:1'
        ]);
        
        $this->loading = true;
        $this->sendResult = null;
        
        try {
            $service = new WhatsAppService();
            $response = $service->sendMessage($this->number, $this->message);
            
            // التأكد من أن الاستجابة array
            if (!is_array($response)) {
                $response = ['success' => false, 'error' => 'Invalid response from server'];
            }
            
            if ($response['success'] ?? false) {
                $this->sendResult = ['success' => true, 'message' => 'Message sent successfully'];
                $this->message = '';
                Log::info('Message sent successfully', ['number' => $this->number]);
            } else {
                $this->sendResult = ['success' => false, 'error' => $response['error'] ?? 'Failed to send message'];
                Log::warning('Failed to send message', ['number' => $this->number, 'error' => $response['error'] ?? 'Unknown']);
            }
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            $this->sendResult = ['success' => false, 'error' => 'Error sending message: ' . $e->getMessage()];
        }
        
        $this->loading = false;
    }

    #[On('refresh-all')]
    public function refreshAll()
    {
        $this->checkResult = null;
        $this->sendResult = null;
        $this->loading = false;
        Log::info('WhatsApp Manager refreshed via header button');
    }

    public function render()
    {
        return view('livewire.whatsapp-manager');
    }
}