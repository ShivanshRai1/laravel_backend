<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialMetric;

class FinancialMetricsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metrics = [
            // Profitability Metrics
            [
                'name' => 'revenue',
                'display_name' => 'Revenue',
                'type' => 'currency',
                'unit' => 'USD',
                'category' => 'Profitability',
                'is_custom' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'gross_profit',
                'display_name' => 'Gross Profit',
                'type' => 'currency',
                'unit' => 'USD',
                'category' => 'Profitability',
                'is_custom' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'operating_income',
                'display_name' => 'Operating Income',
                'type' => 'currency',
                'unit' => 'USD',
                'category' => 'Profitability',
                'is_custom' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'net_income',
                'display_name' => 'Net Income',
                'type' => 'currency',
                'unit' => 'USD',
                'category' => 'Profitability',
                'is_custom' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'gross_margin',
                'display_name' => 'Gross Margin',
                'type' => 'percentage',
                'unit' => '%',
                'category' => 'Profitability',
                'is_custom' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'operating_margin',
                'display_name' => 'Operating Margin',
                'type' => 'percentage',
                'unit' => '%',
                'category' => 'Profitability',
                'is_custom' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'net_margin',
                'display_name' => 'Net Margin',
                'type' => 'percentage',
                'unit' => '%',
                'category' => 'Profitability',
                'is_custom' => false,
                'sort_order' => 7,
            ],

            // Liquidity Ratios
            [
                'name' => 'current_ratio',
                'display_name' => 'Current Ratio',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Liquidity',
                'is_custom' => false,
                'sort_order' => 10,
            ],
            [
                'name' => 'quick_ratio',
                'display_name' => 'Quick Ratio',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Liquidity',
                'is_custom' => false,
                'sort_order' => 11,
            ],
            [
                'name' => 'cash_ratio',
                'display_name' => 'Cash Ratio',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Liquidity',
                'is_custom' => false,
                'sort_order' => 12,
            ],

            // Leverage Ratios
            [
                'name' => 'debt_to_equity',
                'display_name' => 'Debt-to-Equity Ratio',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Leverage',
                'is_custom' => false,
                'sort_order' => 20,
            ],
            [
                'name' => 'debt_to_assets',
                'display_name' => 'Debt-to-Assets Ratio',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Leverage',
                'is_custom' => false,
                'sort_order' => 21,
            ],
            [
                'name' => 'interest_coverage',
                'display_name' => 'Interest Coverage Ratio',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Leverage',
                'is_custom' => false,
                'sort_order' => 22,
            ],

            // Efficiency Ratios
            [
                'name' => 'asset_turnover',
                'display_name' => 'Asset Turnover',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Efficiency',
                'is_custom' => false,
                'sort_order' => 30,
            ],
            [
                'name' => 'inventory_turnover',
                'display_name' => 'Inventory Turnover',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Efficiency',
                'is_custom' => false,
                'sort_order' => 31,
            ],
            [
                'name' => 'receivables_turnover',
                'display_name' => 'Receivables Turnover',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Efficiency',
                'is_custom' => false,
                'sort_order' => 32,
            ],

            // Valuation Metrics
            [
                'name' => 'pe_ratio',
                'display_name' => 'P/E Ratio',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Valuation',
                'is_custom' => false,
                'sort_order' => 40,
            ],
            [
                'name' => 'price_to_book',
                'display_name' => 'Price-to-Book Ratio',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Valuation',
                'is_custom' => false,
                'sort_order' => 41,
            ],
            [
                'name' => 'price_to_sales',
                'display_name' => 'Price-to-Sales Ratio',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Valuation',
                'is_custom' => false,
                'sort_order' => 42,
            ],
            [
                'name' => 'ev_to_ebitda',
                'display_name' => 'EV/EBITDA',
                'type' => 'ratio',
                'unit' => 'x',
                'category' => 'Valuation',
                'is_custom' => false,
                'sort_order' => 43,
            ],

            // Return Metrics
            [
                'name' => 'roa',
                'display_name' => 'Return on Assets (ROA)',
                'type' => 'percentage',
                'unit' => '%',
                'category' => 'Returns',
                'is_custom' => false,
                'sort_order' => 50,
            ],
            [
                'name' => 'roe',
                'display_name' => 'Return on Equity (ROE)',
                'type' => 'percentage',
                'unit' => '%',
                'category' => 'Returns',
                'is_custom' => false,
                'sort_order' => 51,
            ],
            [
                'name' => 'roic',
                'display_name' => 'Return on Invested Capital (ROIC)',
                'type' => 'percentage',
                'unit' => '%',
                'category' => 'Returns',
                'is_custom' => false,
                'sort_order' => 52,
            ],
        ];

        foreach ($metrics as $metric) {
            FinancialMetric::updateOrCreate(
                ['name' => $metric['name']],
                $metric
            );
        }

        $this->command->info('Financial metrics seeded successfully!');
    }
}
