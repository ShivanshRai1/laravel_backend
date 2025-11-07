<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InsightsService
{
    /**
     * Generate insights for a company based on financial data
     */
    public function generateInsights(string $ticker): array
    {
        $insights = [];

        try {
            // Get recent financial data for the company
            $recentData = $this->getRecentFinancialData($ticker);
            
            if (empty($recentData)) {
                return [];
            }

            // Calculate YoY changes
            $yoyInsights = $this->calculateYoYInsights($recentData);
            $insights = array_merge($insights, $yoyInsights);

            // Calculate QoQ trends
            $qoqInsights = $this->calculateQoQInsights($recentData);
            $insights = array_merge($insights, $qoqInsights);

            // Detect trends
            $trendInsights = $this->detectTrends($recentData);
            $insights = array_merge($insights, $trendInsights);

            // Identify anomalies
            $anomalyInsights = $this->detectAnomalies($recentData);
            $insights = array_merge($insights, $anomalyInsights);

        } catch (\Exception $e) {
            \Log::error("Error generating insights for {$ticker}: " . $e->getMessage());
        }

        return $insights;
    }

    /**
     * Get recent financial data for a company
     */
    private function getRecentFinancialData(string $ticker): array
    {
        try {
            // Get company by symbol (matches frontend and DB)
            $company = DB::table('companies')
                ->where('symbol', $ticker)
                ->first();

            if (!$company) {
                return [];
            }

            // Get last 8 quarters of data (by date)
            $data = DB::table('financial_data')
                ->where('company_id', $company->id)
                ->orderBy('date', 'desc')
                ->limit(8)
                ->get()
                ->toArray();

            return array_reverse($data); // Oldest to newest

        } catch (\Exception $e) {
            \Log::error("Error fetching financial data: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate Year-over-Year insights
     */
    private function calculateYoYInsights(array $data): array
    {
        $insights = [];

        if (count($data) < 5) {
            return $insights;
        }

        // Compare latest quarter with same quarter last year (4 quarters ago)
        $current = end($data);
        $yearAgo = $data[count($data) - 5];

            // Revenue YoY
            if (isset($current->revenue) && isset($yearAgo->revenue) && $yearAgo->revenue > 0) {
                $change = (($current->revenue - $yearAgo->revenue) / $yearAgo->revenue) * 100;
                $insights[] = [
                    'type' => 'yoy',
                    'metric' => 'Revenue',
                    'value' => $current->revenue,
                    'change_pct' => round($change, 2),
                    'trend' => $change > 0 ? 'up' : 'down',
                    'description' => sprintf(
                        'Revenue %s %.1f%% YoY to $%.2fB',
                        $change > 0 ? 'increased' : 'decreased',
                        abs($change),
                        $current->revenue / 1000000000
                    )
                ];
            }

            // Profit YoY
            if (isset($current->profit) && isset($yearAgo->profit) && $yearAgo->profit > 0) {
                $change = (($current->profit - $yearAgo->profit) / $yearAgo->profit) * 100;
                $insights[] = [
                    'type' => 'yoy',
                    'metric' => 'Profit',
                    'value' => $current->profit,
                    'change_pct' => round($change, 2),
                    'trend' => $change > 0 ? 'up' : 'down',
                    'description' => sprintf(
                        'Profit %s %.1f%% YoY',
                        $change > 0 ? 'grew' : 'declined',
                        abs($change)
                    )
                ];
            }

        // Gross Profit YoY
        if (isset($current->gross_profit) && isset($yearAgo->gross_profit) && $yearAgo->gross_profit > 0) {
            $change = (($current->gross_profit - $yearAgo->gross_profit) / $yearAgo->gross_profit) * 100;
            if (abs($change) > 5) { // Only show if significant
                $insights[] = [
                    'type' => 'yoy',
                    'metric' => 'Gross Profit',
                    'value' => $current->gross_profit,
                    'change_pct' => round($change, 2),
                    'trend' => $change > 0 ? 'up' : 'down',
                    'description' => sprintf(
                        'Gross profit %s %.1f%% YoY',
                        $change > 0 ? 'increased' : 'decreased',
                        abs($change)
                    )
                ];
            }
        }

        return $insights;
    }

    /**
     * Calculate Quarter-over-Quarter insights
     */
    private function calculateQoQInsights(array $data): array
    {
        $insights = [];

        if (count($data) < 2) {
            return $insights;
        }

        $current = end($data);
        $previous = $data[count($data) - 2];

        // Revenue QoQ
        if (isset($current->total_revenue) && isset($previous->total_revenue) && $previous->total_revenue > 0) {
            $change = (($current->total_revenue - $previous->total_revenue) / $previous->total_revenue) * 100;
            if (abs($change) > 3) { // Only show if significant
                $insights[] = [
                    'type' => 'qoq',
                    'metric' => 'Revenue',
                    'value' => $current->total_revenue,
                    'change_pct' => round($change, 2),
                    'trend' => $change > 0 ? 'up' : 'down',
                    'description' => sprintf(
                        'Sequential revenue %s %.1f%% QoQ',
                        $change > 0 ? 'growth' : 'decline',
                        abs($change)
                    )
                ];
            }
        }

        return $insights;
    }

    /**
     * Detect trends over multiple quarters
     */
    private function detectTrends(array $data): array
    {
        $insights = [];

        if (count($data) < 4) {
            return $insights;
        }

        // Check margin trends
        $margins = [];
        foreach (array_slice($data, -4) as $quarter) {
            if (isset($quarter->total_revenue) && isset($quarter->net_income) && $quarter->total_revenue > 0) {
                $margins[] = ($quarter->net_income / $quarter->total_revenue) * 100;
            }
        }

        if (count($margins) >= 3) {
            $isImproving = true;
            $isDeclining = true;
            
            for ($i = 1; $i < count($margins); $i++) {
                if ($margins[$i] <= $margins[$i-1]) {
                    $isImproving = false;
                }
                if ($margins[$i] >= $margins[$i-1]) {
                    $isDeclining = false;
                }
            }

            if ($isImproving) {
                $insights[] = [
                    'type' => 'trend',
                    'metric' => 'Profit Margin',
                    'value' => end($margins),
                    'change_pct' => null,
                    'trend' => 'improving',
                    'description' => sprintf(
                        'Profit margins improving over last %d quarters (currently %.1f%%)',
                        count($margins),
                        end($margins)
                    )
                ];
            } elseif ($isDeclining) {
                $insights[] = [
                    'type' => 'trend',
                    'metric' => 'Profit Margin',
                    'value' => end($margins),
                    'change_pct' => null,
                    'trend' => 'declining',
                    'description' => sprintf(
                        'Profit margins declining over last %d quarters (currently %.1f%%)',
                        count($margins),
                        end($margins)
                    )
                ];
            }
        }

        return $insights;
    }

    /**
     * Detect anomalies or unusual patterns
     */
    private function detectAnomalies(array $data): array
    {
        $insights = [];

        if (count($data) < 4) {
            return $insights;
        }

        $current = end($data);
        $previous = array_slice($data, -4, 3);

        // Check for unusual revenue spike/drop
        if (isset($current->total_revenue) && !empty($previous)) {
            $avgPrevious = array_sum(array_column($previous, 'total_revenue')) / count($previous);
            
            if ($avgPrevious > 0) {
                $change = (($current->total_revenue - $avgPrevious) / $avgPrevious) * 100;
                
                if ($change > 20) {
                    $insights[] = [
                        'type' => 'anomaly',
                        'metric' => 'Revenue',
                        'value' => $current->total_revenue,
                        'change_pct' => round($change, 2),
                        'trend' => 'alert',
                        'description' => sprintf(
                            'Significant revenue spike: %.1f%% above recent average',
                            $change
                        )
                    ];
                } elseif ($change < -20) {
                    $insights[] = [
                        'type' => 'anomaly',
                        'metric' => 'Revenue',
                        'value' => $current->total_revenue,
                        'change_pct' => round($change, 2),
                        'trend' => 'alert',
                        'description' => sprintf(
                            'Significant revenue drop: %.1f%% below recent average',
                            abs($change)
                        )
                    ];
                }
            }
        }

        return $insights;
    }
}
