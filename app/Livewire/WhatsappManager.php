<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WhatsAppService;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class WhatsappManager extends Component
{
    public $number = '';
    public $numbers = '';
    public $message = '';
    public $checkResult = null;
    public $sendResult = null;
    public $bulkCheckResults = [];
    public $loading = false;
    public $bulkLoading = false;
    public $checkMode = 'single'; // 'single' or 'bulk'

    public function checkNumber()
    {
        $this->validate(['number' => 'required|string']);
        
        $this->loading = true;
        $this->checkResult = null;
        
        try {
            $service = new WhatsAppService();
            $response = $service->checkNumber($this->number);
            
            // Ensure response is array
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

    public function checkBulkNumbers()
    {
        $this->validate(['numbers' => 'required|string']);
        
        $this->bulkLoading = true;
        $this->bulkCheckResults = [];
        
        try {
            // Parse numbers from textarea
            $numbersList = $this->parseNumbers($this->numbers);
            
            if (empty($numbersList)) {
                $this->bulkCheckResults = [['error' => 'No valid numbers found']];
                $this->bulkLoading = false;
                return;
            }

            $service = new WhatsAppService();
            $results = [];
            
            foreach ($numbersList as $index => $number) {
                try {
                    Log::info("Checking number " . ($index + 1) . "/" . count($numbersList) . ": {$number}");
                    
                    $response = $service->checkNumber($number);
                    
                    // Ensure response is array
                    if (!is_array($response)) {
                        $response = ['error' => 'Invalid response from server'];
                    }
                    
                    $results[] = [
                        'number' => $number,
                        'exists' => $response['exists'] ?? false,
                        'message' => $response['message'] ?? ($response['error'] ?? 'Unknown result'),
                        'status' => ($response['exists'] ?? false) ? 'valid' : 'invalid',
                        'checked_at' => now()->format('H:i:s')
                    ];
                    
                    Log::info('Bulk number checked', ['number' => $number, 'result' => $response]);
                    
                    // Small delay to avoid rate limiting
                    usleep(500000); // 0.5 seconds
                    
                } catch (\Exception $e) {
                    Log::error("Error checking number {$number}: " . $e->getMessage());
                    $results[] = [
                        'number' => $number,
                        'exists' => false,
                        'message' => 'Error: ' . $e->getMessage(),
                        'status' => 'error',
                        'checked_at' => now()->format('H:i:s')
                    ];
                }
            }
            
            $this->bulkCheckResults = $results;
            
            Log::info('Bulk check completed', [
                'total_numbers' => count($numbersList),
                'valid_numbers' => count(array_filter($results, fn($r) => $r['exists'])),
                'invalid_numbers' => count(array_filter($results, fn($r) => !$r['exists']))
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in bulk check: ' . $e->getMessage());
            $this->bulkCheckResults = [['error' => 'Error in bulk check: ' . $e->getMessage()]];
        }
        
        $this->bulkLoading = false;
    }

    private function parseNumbers($numbersText)
    {
        // Split by new lines, commas, or semicolons
        $numbers = preg_split('/[\r\n,;]+/', trim($numbersText));
        
        // Clean and filter numbers
        $cleanNumbers = [];
        foreach ($numbers as $number) {
            $number = trim($number);
            if (!empty($number)) {
                // Remove any non-digit characters except + at the beginning
                $cleanNumber = preg_replace('/[^\d+]/', '', $number);
                if (strlen($cleanNumber) >= 10) { // Minimum phone number length
                    $cleanNumbers[] = $cleanNumber;
                }
            }
        }
        
        // Remove duplicates
        return array_unique($cleanNumbers);
    }

    public function switchMode($mode)
    {
        $this->checkMode = $mode;
        $this->clearResults();
    }

    public function clearResults()
    {
        $this->checkResult = null;
        $this->sendResult = null;
        $this->bulkCheckResults = [];
    }



    public function exportResults()
    {
        if (empty($this->bulkCheckResults)) {
            return;
        }

        $csv = "Number,Status,Message,Checked At\n";
        foreach ($this->bulkCheckResults as $result) {
            $csv .= sprintf(
                '"%s","%s","%s","%s"' . "\n",
                $result['number'],
                $result['status'],
                str_replace('"', '""', $result['message']),
                $result['checked_at']
            );
        }

        $filename = 'whatsapp_check_results_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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
            
            // Ensure response is array
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
        $this->bulkCheckResults = [];
        $this->loading = false;
        $this->bulkLoading = false;
        Log::info('WhatsApp Manager refreshed via header button');
    }

    public function render()
    {
        return view('livewire.whatsapp-manager');
    }
}