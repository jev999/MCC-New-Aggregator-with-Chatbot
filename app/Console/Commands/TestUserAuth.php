<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserAuth extends Command
{
    protected $signature = 'test:user-auth {email} {password}';
    protected $description = 'Test user authentication with given credentials';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $this->info("Testing authentication for: {$email}");
        
        // Get all users and check for matches
        $users = User::all();
        $this->info("Total users in database: " . $users->count());
        
        $foundUser = null;
        foreach ($users as $user) {
            if ($user->ms365_account === $email || $user->gmail_account === $email) {
                $foundUser = $user;
                break;
            }
        }
        
        if ($foundUser) {
            $this->info("✓ User found!");
            $this->info("  - ID: {$foundUser->id}");
            $this->info("  - MS365 Account: {$foundUser->ms365_account}");
            $this->info("  - Gmail Account: {$foundUser->gmail_account}");
            $this->info("  - Name: {$foundUser->first_name} {$foundUser->surname}");
            $this->info("  - Role: {$foundUser->role}");
            
            // Test password
            $passwordCheck = Hash::check($password, $foundUser->password);
            if ($passwordCheck) {
                $this->info("✓ Password verification successful!");
            } else {
                $this->error("✗ Password verification failed!");
                $this->info("  - Password hash: " . substr($foundUser->password, 0, 50) . "...");
                
                // Test common passwords
                $commonPasswords = ['password', '123456', 'admin123', 'test123', 'user123'];
                $this->info("Testing common passwords:");
                foreach ($commonPasswords as $testPass) {
                    if (Hash::check($testPass, $foundUser->password)) {
                        $this->info("  ✓ Password is: {$testPass}");
                        return;
                    }
                }
                $this->info("  - None of the common passwords match");
            }
        } else {
            $this->error("✗ User not found with email: {$email}");
            $this->info("Available emails:");
            foreach ($users->take(5) as $user) {
                $this->info("  - MS365: {$user->ms365_account}");
                $this->info("  - Gmail: {$user->gmail_account}");
            }
        }
    }
}
