<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\UploadedFinancialData;

class FinancialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedUploadedFinancialData();
        $this->seedFinancialDataTable();
    }

    private function seedUploadedFinancialData()
    {
        $companies = ['NXP Semiconductors', 'Onsemi', 'Texas Instruments', 'Analog Devices', 
                     'Vishay Intertechnology', 'Infineon Technologies', 'Rohm Semiconductor', 
                     'Apple Inc', 'Microsoft Corporation', 'Amazon.com Inc'];
        $metrics = [
            'Total Revenue',
            'Net Income',
            'Total Assets', 
            'Total Debt',
            'Shareholders Equity',
            'Operating Income',
            'Gross Profit',
            'Operating Expenses',
            'Cost of Revenue'
        ];
        foreach ($companies as $company) {
            foreach ($metrics as $metric) {
                $baseValue = $this->getBaseValue($company, $metric);
                \App\Models\UploadedFinancialData::create([
                    'Company' => $company,
                    'Metrics' => $metric,
                    'Currency' => 'USD',
                    'CY_2022_Q4' => round($baseValue * 1.0, 2),
                    'CY_2023_Q1' => round($baseValue * 0.95, 2), 
                    'CY_2023_Q2' => round($baseValue * 1.02, 2),
                    'CY_2023_Q3' => round($baseValue * 1.08, 2),
                    'CY_2023_Q4' => round($baseValue * 1.15, 2),
                    'CY_2024_Q1' => round($baseValue * 1.12, 2),
                    'CY_2024_Q2' => round($baseValue * 1.18, 2),
                    'CY_2024_Q3' => round($baseValue * 1.25, 2),
                    'CY_2024_Q4' => round($baseValue * 1.30, 2),
                    'CY_2025_Q1' => round($baseValue * 1.35, 2),
                    'original_filename' => 'seeded_data.csv',
                    'uploaded_by' => 1,
                    'uploaded_at' => now(),
                ]);
            }
        }
    }

    private function seedFinancialDataTable()
    {
        // Seed 4 quarters for all companies, only if not already present
        $quarters = [
            ['date' => '2024-01-01', 'revenue' => 100000000, 'profit' => 20000000, 'market_cap' => 500000000, 'pe_ratio' => 20, 'dividend_yield' => 1.5, 'eps' => 2.5],
            ['date' => '2024-04-01', 'revenue' => 110000000, 'profit' => 22000000, 'market_cap' => 520000000, 'pe_ratio' => 21, 'dividend_yield' => 1.6, 'eps' => 2.7],
            ['date' => '2024-07-01', 'revenue' => 120000000, 'profit' => 25000000, 'market_cap' => 540000000, 'pe_ratio' => 22, 'dividend_yield' => 1.7, 'eps' => 2.9],
            ['date' => '2024-10-01', 'revenue' => 130000000, 'profit' => 27000000, 'market_cap' => 560000000, 'pe_ratio' => 23, 'dividend_yield' => 1.8, 'eps' => 3.0],
        ];
        $companies = \App\Models\Company::all();
        foreach ($companies as $company) {
            $hasData = \DB::table('financial_data')->where('company_id', $company->id)->exists();
            if (!$hasData) {
                foreach ($quarters as $q) {
                    \DB::table('financial_data')->insert([
                        'company_id' => $company->id,
                        'date' => $q['date'],
                        'revenue' => $q['revenue'],
                        'profit' => $q['profit'],
                        'market_cap' => $q['market_cap'],
                        'pe_ratio' => $q['pe_ratio'],
                        'dividend_yield' => $q['dividend_yield'],
                        'eps' => $q['eps'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
    
    /**
     * Get base value for company and metric (in millions USD)
     */
    private function getBaseValue($company, $metric): float
    {
        $companyMultipliers = [
            'Apple Inc' => 100000,  // Large tech company
            'Microsoft Corporation' => 80000,
            'Amazon.com Inc' => 120000,
            'NXP Semiconductors' => 3000,  // Mid-size semiconductor
            'Texas Instruments' => 4000,
            'Analog Devices' => 2500,
            'Onsemi' => 2000,
            'Vishay Intertechnology' => 800,  // Smaller semiconductor
            'Infineon Technologies' => 2800,
            'Rohm Semiconductor' => 1200,
        ];
        
        $metricMultipliers = [
            'Total Revenue' => 1.0,
            'Net Income' => 0.15,      // 15% of revenue
            'Total Assets' => 1.5,     // 150% of revenue
            'Total Debt' => 0.3,       // 30% of revenue
            'Shareholders Equity' => 1.0,  // 100% of revenue
            'Operating Income' => 0.20,    // 20% of revenue
            'Gross Profit' => 0.35,       // 35% of revenue
            'Operating Expenses' => 0.15,  // 15% of revenue
            'Cost of Revenue' => 0.65,    // 65% of revenue
        ];
        
        $baseCompanyValue = $companyMultipliers[$company] ?? 1000;
        $metricRatio = $metricMultipliers[$metric] ?? 0.1;
        
        return $baseCompanyValue * $metricRatio;
    }
}