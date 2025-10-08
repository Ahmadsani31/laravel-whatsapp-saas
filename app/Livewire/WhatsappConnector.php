<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WhatsAppService;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class WhatsappConnector extends Component
{
    public $status = 'disconnected';
    public $qr = null;
    public $message = null;
    public $messageType = null;
    public $autoRefresh = true;

    public function mount()
    {
        $this->getStatus();
    }

    public function getStatus()
    {
        try {
            $service = new WhatsAppService();
            $response = $service->getStatus();
            
            if (!is_array($response)) {
                Log::warning('Invalid response format from WhatsApp service', ['response' => $response]);
                $response = ['status' => 'error', 'error' => 'Invalid response format'];
            }
            
            $oldStatus = $this->status;
            $this->status = $response['status'] ?? 'disconnected';
            $this->qr = $response['qr'] ?? null;
            
            // Auto-refresh status when connected/disconnected
            if ($oldStatus !== $this->status) {
                $this->handleStatusChange($oldStatus, $this->status);
            }
            
            if ($this->status === 'error') {
                $this->dispatch('status-error', ['message' => $response['error'] ?? 'Unknown error']);
            }
            
            Log::info('Status updated', ['status' => $this->status, 'has_qr' => !empty($this->qr)]);
        } catch (\Exception $e) {
            Log::error('Error getting status: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $this->showMessage('Connection error: ' . $e->getMessage(), 'error');
            $this->dispatch('status-error', ['message' => $e->getMessage()]);
        }
    }

    public function disconnect()
    {
        try {
            $service = new WhatsAppService();
            $response = $service->disconnect();
            
            if (!is_array($response)) {
                $response = ['success' => false, 'error' => 'Invalid response'];
            }
            
            if ($response['success'] ?? false) {
                $this->showMessage('Disconnected successfully', 'success');
                $this->status = 'disconnected';
                $this->qr = null;
            } else {
                $this->showMessage($response['error'] ?? 'Failed to disconnect', 'error');
            }
        } catch (\Exception $e) {
            Log::error('Error disconnecting: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $this->showMessage('Disconnect error: ' . $e->getMessage(), 'error');
            $this->dispatch('status-error', ['message' => $e->getMessage()]);
        }
    }

    #[On('statusUpdated')]
    public function updateStatus($data = [])
    {
        $oldStatus = $this->status;
        
        if (is_array($data) && isset($data['status'])) {
            $this->status = $data['status'];
            $this->clearMessage();
            Log::info('Status updated via Socket.IO', ['status' => $data['status']]);
        } elseif (is_string($data)) {
            $this->status = $data;
            $this->clearMessage();
            Log::info('Status updated via Socket.IO (legacy)', ['status' => $data]);
        }
        
        // Handle status changes
        if ($oldStatus !== $this->status) {
            $this->handleStatusChange($oldStatus, $this->status);
        }
    }

    #[On('qrUpdated')]
    public function updateQr($data = [])
    {
        if (is_array($data) && array_key_exists('qr', $data)) {
            $this->qr = $data['qr'];
            Log::info('QR updated via Socket.IO', ['has_qr' => !empty($data['qr'])]);
        } elseif (is_string($data) || is_null($data)) {
            $this->qr = $data;
            Log::info('QR updated via Socket.IO (legacy)', ['has_qr' => !empty($data)]);
        }
    }

    #[On('refresh-all')]
    public function refreshAll()
    {
        $this->getStatus();
        $this->clearMessage();
        Log::info('WhatsApp Connector refreshed via header button');
    }

    private function handleStatusChange($oldStatus, $newStatus)
    {
        if ($newStatus === 'connected' && $oldStatus !== 'connected') {
            $this->showMessage('WhatsApp connected successfully!', 'success');
            $this->dispatch('whatsapp-connected');
        } elseif ($newStatus === 'disconnected' && $oldStatus === 'connected') {
            $this->showMessage('WhatsApp disconnected', 'warning');
            $this->dispatch('whatsapp-disconnected');
        } elseif ($newStatus === 'qr') {
            $this->showMessage('Please scan the QR code with your phone', 'info');
        }
    }

    private function showMessage($text, $type = 'info')
    {
        $this->message = $text;
        $this->messageType = $type;
    }

    private function clearMessage()
    {
        $this->message = null;
        $this->messageType = null;
    }

    public function render()
    {
        return view('livewire.whatsapp-connector');
    }
}