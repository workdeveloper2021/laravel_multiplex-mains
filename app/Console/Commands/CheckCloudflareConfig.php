<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCloudflareConfig extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cloudflare:check-config';

    /**
     * The console command description.
     */
    protected $description = 'Check Cloudflare Stream configuration and test API connectivity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Cloudflare Stream configuration...');
        
        // Check environment variables
        $accountId = config('services.cloudflare.CLOUDFLARE_ACCOUNT_ID');
        $apiToken = config('services.cloudflare.CLOUDFLARE_API_TOKEN');
        $baseUrl = config('services.cloudflare.base_url');
        
        $this->info("Environment: " . app()->environment());
        $this->info("Base URL: " . ($baseUrl ?: 'Not set'));
        
        if (empty($accountId)) {
            $this->error('❌ CLOUDFLARE_ACCOUNT_ID is not set in .env');
        } else {
            $this->info('✅ CLOUDFLARE_ACCOUNT_ID is set: ' . substr($accountId, 0, 8) . '...');
        }
        
        if (empty($apiToken)) {
            $this->error('❌ CLOUDFLARE_API_TOKEN is not set in .env');
        } else {
            $this->info('✅ CLOUDFLARE_API_TOKEN is set: ' . substr($apiToken, 0, 8) . '...');
        }
        
        // Test API connectivity
        if (!empty($accountId) && !empty($apiToken)) {
            $this->info('Testing API connectivity...');
            
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/accounts/{$accountId}/stream");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiToken,
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($error) {
                    $this->error('❌ cURL Error: ' . $error);
                } else if ($httpCode === 200) {
                    $this->info('✅ API connectivity successful (HTTP ' . $httpCode . ')');
                } else {
                    $this->error('❌ API Error: HTTP ' . $httpCode);
                    $this->error('Response: ' . substr($response, 0, 200));
                }
                
            } catch (\Exception $e) {
                $this->error('❌ Exception: ' . $e->getMessage());
            }
        }
        
        $this->info('Configuration check complete.');
        
        return 0;
    }
}
