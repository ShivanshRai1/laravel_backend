<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Company;
use App\Models\Watchlist;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class SetupTestEnvironment extends Command
{
    protected $signature = 'app:setup-test';
    protected $description = 'Setup test environment with users, companies, and watchlist entries';

    public function handle()
    {
        $this->info('Setting up test environment...');
        
        // Create a test user if not exists
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'user'
            ]
        );
        
        $this->info("Test user: {$user->email} / password");
        
        // Create NXP Semiconductors if it doesn't exist
        $nxp = Company::firstOrCreate(
            ['symbol' => 'NXPI'],
            [
                'name' => 'NXP Semiconductors',
                'industry' => 'Semiconductors',
                'sector' => 'Semiconductors',
            ]
        );
        
        $this->info("Added NXP Semiconductors company: ID={$nxp->id}, Symbol={$nxp->symbol}");
        
        // Generate a token for testing
        $token = JWTAuth::fromUser($user);
        $this->info("JWT Token for testing: $token");
        $this->info("\nTo use this token in your browser console:");
        $this->info("localStorage.setItem('token', '$token');");
        
        // Add companies to watchlist for testing if needed
        $companies = Company::take(3)->get();
        foreach ($companies as $company) {
            Watchlist::firstOrCreate([
                'user_id' => $user->id,
                'company_id' => $company->symbol
            ], [
                'company_name' => $company->name,
                'company_ticker' => $company->symbol,
                'uuid' => \Illuminate\Support\Str::uuid()->toString()
            ]);
        }
        
        $this->info("Added {$companies->count()} companies to test user's watchlist");
        
        return Command::SUCCESS;
    }
}