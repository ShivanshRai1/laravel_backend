<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MonitorCorsLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cors:monitor {--lines=50 : Number of lines to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor CORS-related logs in real time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lines = $this->option('lines');
        $logFile = storage_path('logs/laravel.log');

        if (!File::exists($logFile)) {
            $this->error('Log file not found: ' . $logFile);
            return 1;
        }

        $this->info('Monitoring CORS logs (press Ctrl+C to exit)...');
        $this->newLine();

        // Show recent CORS-related logs
        $this->showRecentCorsLogs($logFile, $lines);

        // Monitor for new logs (simplified version)
        $this->info('Use "tail -f storage/logs/laravel.log | grep CORS" for real-time monitoring');

        return 0;
    }

    private function showRecentCorsLogs(string $logFile, int $lines)
    {
        $command = "tail -n {$lines} \"{$logFile}\" | findstr /i \"cors\"";
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "powershell \"Get-Content '{$logFile}' -Tail {$lines} | Select-String -Pattern 'CORS' -CaseSensitive:$false\"";
        }

        $this->info('Recent CORS-related logs:');
        $this->line('----------------------------------------');
        
        $output = shell_exec($command);
        if ($output) {
            $this->line($output);
        } else {
            $this->info('No recent CORS logs found.');
        }
    }
}