<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailAlertPreference;
use App\Models\AlertHistory;
use App\Models\UploadedFinancialData;
use App\Models\Company;
use App\Mail\FinancialAlertMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckFinancialAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting financial alerts check');

        // Get all enabled email alert preferences
        $alerts = EmailAlertPreference::where('enabled', true)
            ->with('user')
            ->get();

        foreach ($alerts as $alert) {
            try {
                $this->checkAlert($alert);
            } catch (\Exception $e) {
                Log::error('Error checking alert: ' . $e->getMessage(), [
                    'alert_id' => $alert->id,
                    'user_id' => $alert->user_id
                ]);
            }
        }

        Log::info('Finished financial alerts check');
    }

    private function checkAlert(EmailAlertPreference $alert): void
    {
        // Get companies to check
        $companies = [];
        
        if ($alert->company_id) {
            // Specific company
            $company = Company::where('symbol', $alert->company_id)->first();
            if ($company) {
                $companies[] = $company;
            }
        } else {
            // All companies in user's watchlist
            $companies = $alert->user->watchlists()->get()->map(function ($watchlist) {
                return Company::where('symbol', $watchlist->company_id)->first();
            })->filter();
        }

        foreach ($companies as $company) {
            // Check for new data
            if (in_array($alert->alert_type, ['new_data', 'all'])) {
                $this->checkNewData($alert, $company);
            }

            // Check for ratio changes
            if (in_array($alert->alert_type, ['ratio_change', 'all'])) {
                $this->checkRatioChanges($alert, $company);
            }
        }
    }

    private function checkNewData(EmailAlertPreference $alert, Company $company): void
    {
        // Check if there's new financial data uploaded in the last 24 hours
        $recentData = UploadedFinancialData::where('company_id', $company->id)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($recentData) {
            // Check if we already sent this alert recently
            $alreadySent = AlertHistory::where('user_id', $alert->user_id)
                ->where('company_id', $company->symbol)
                ->where('alert_type', 'new_data')
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if (!$alreadySent) {
                $this->sendAlert($alert, $company, 'new_data', [
                    'message' => "New financial data has been uploaded for {$company->name} ({$company->symbol})"
                ]);
            }
        }
    }

    private function checkRatioChanges(EmailAlertPreference $alert, Company $company): void
    {
        // Get the latest two quarters of data
        $financialData = UploadedFinancialData::where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        if ($financialData->count() < 2) {
            return; // Not enough data to compare
        }

        $latest = $financialData->first();
        $previous = $financialData->last();

        // Compare key ratios
        $changes = [];
        $quarters = ['CY_2025_Q1', 'CY_2024_Q4', 'CY_2024_Q3', 'CY_2024_Q2'];
        
        foreach ($quarters as $quarter) {
            if (isset($latest->$quarter) && isset($previous->$quarter)) {
                $latestValue = floatval($latest->$quarter);
                $previousValue = floatval($previous->$quarter);
                
                if ($previousValue != 0) {
                    $changePercent = (($latestValue - $previousValue) / abs($previousValue)) * 100;
                    
                    if (abs($changePercent) >= $alert->threshold) {
                        $changes[] = [
                            'metric' => $latest->Metrics,
                            'quarter' => $quarter,
                            'change_percent' => round($changePercent, 2),
                            'latest_value' => $latestValue,
                            'previous_value' => $previousValue
                        ];
                    }
                }
            }
        }

        if (!empty($changes)) {
            // Check if we already sent this alert recently
            $alreadySent = AlertHistory::where('user_id', $alert->user_id)
                ->where('company_id', $company->symbol)
                ->where('alert_type', 'ratio_change')
                ->where('created_at', '>=', now()->subHours(6))
                ->exists();

            if (!$alreadySent) {
                $this->sendAlert($alert, $company, 'ratio_change', [
                    'message' => "Significant ratio changes detected for {$company->name} ({$company->symbol})",
                    'changes' => $changes
                ]);
            }
        }
    }

    private function sendAlert(EmailAlertPreference $alert, Company $company, string $alertType, array $content): void
    {
        try {
            // Create alert history record
            $history = AlertHistory::create([
                'user_id' => $alert->user_id,
                'email_alert_preference_id' => $alert->id,
                'company_id' => $company->symbol,
                'alert_type' => $alertType,
                'alert_content' => json_encode($content),
                'sent' => false
            ]);

            // Send email
            Mail::to($alert->user->email)->send(new FinancialAlertMail($alert->user, $company, $alertType, $content));

            // Mark as sent
            $history->update([
                'sent' => true,
                'sent_at' => now()
            ]);

            Log::info('Alert sent successfully', [
                'user_id' => $alert->user_id,
                'company' => $company->symbol,
                'alert_type' => $alertType
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send alert email: ' . $e->getMessage(), [
                'user_id' => $alert->user_id,
                'company' => $company->symbol
            ]);
        }
    }
}
