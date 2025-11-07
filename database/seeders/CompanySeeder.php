<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [ 'name' => 'Onsemi', 'symbol' => 'ON', 'industry' => 'Semiconductors' ],
            [ 'name' => 'Texas Instruments', 'symbol' => 'TI', 'industry' => 'Semiconductors' ],
            [ 'name' => 'Analog Devices', 'symbol' => 'ADI', 'industry' => 'Semiconductors' ],
            [ 'name' => 'Vishay Intertechnology', 'symbol' => 'VSH', 'industry' => 'Semiconductors' ],
            [ 'name' => 'Infineon Technologies', 'symbol' => 'IFX', 'industry' => 'Semiconductors' ],
            [ 'name' => 'Rohm Semiconductor', 'symbol' => 'ROHM', 'industry' => 'Semiconductors' ],
            [ 'name' => 'NXP Semiconductors', 'symbol' => 'NXPI', 'industry' => 'Semiconductors' ],
            [ 'name' => 'Apple Inc', 'symbol' => 'AAPL', 'industry' => 'Technology' ],
            [ 'name' => 'Microsoft Corporation', 'symbol' => 'MSFT', 'industry' => 'Technology' ],
            [ 'name' => 'Amazon.com Inc', 'symbol' => 'AMZN', 'industry' => 'E-commerce' ]
        ];

        foreach ($companies as $company) {
            $existing = \App\Models\Company::where('symbol', $company['symbol'])->first();
            if (!$existing) {
                \App\Models\Company::create($company);
            }
        }
    }
}
