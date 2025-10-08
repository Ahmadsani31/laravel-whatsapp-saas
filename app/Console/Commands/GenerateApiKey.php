<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiKey;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-key:generate 
                            {name : Name for the API key}
                            {--permissions=* : Permissions for the API key}
                            {--expires= : Expiration date (Y-m-d H:i:s)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API key for AI agents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $permissions = $this->option('permissions');
        $expires = $this->option('expires');

        $expiresAt = null;
        if ($expires) {
            try {
                $expiresAt = \Carbon\Carbon::parse($expires);
            } catch (\Exception) {
                $this->error('Invalid expiration date format. Use: Y-m-d H:i:s');
                return 1;
            }
        }

        $apiKey = ApiKey::generate($name, $permissions, $expiresAt);

        $this->info('API Key generated successfully!');
        $this->line('');
        $this->line('Name: ' . $apiKey->name);
        $this->line('Key: ' . $apiKey->key);
        $this->line('Permissions: ' . (empty($permissions) ? 'All (*)' : implode(', ', $permissions)));
        $this->line('Expires: ' . ($expiresAt ? $expiresAt->format('Y-m-d H:i:s') : 'Never'));
        $this->line('');
        $this->warn('⚠️  Save this key securely - it will not be shown again!');

        return 0;
    }
}