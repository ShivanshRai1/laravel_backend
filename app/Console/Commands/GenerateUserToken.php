<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class GenerateUserToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:token {email : The email of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a JWT token for a user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }
        
        $token = JWTAuth::fromUser($user);
        
        $this->info("Token for user {$user->name} ({$user->email}):");
        $this->line($token);
        
        // Output an example of how to use this token
        $this->info("\nUse this token in your requests:");
        $this->line("Authorization: Bearer {$token}");
        
        // Output the exact JavaScript code to use
        $this->info("\nFor testing in the browser console:");
        $this->line("localStorage.setItem('token', '{$token}')");
        $this->line("localStorage.setItem('user', JSON.stringify(" . json_encode([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]) . "))");
        
        return 0;
    }
}