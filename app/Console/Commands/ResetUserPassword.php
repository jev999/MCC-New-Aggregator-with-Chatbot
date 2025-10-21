<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    protected $signature = 'reset:user-password {email} {password}';
    protected $description = 'Reset user password for testing';

    public function handle()
    {
        $email = $this->argument('email');
        $newPassword = $this->argument('password');
        
        $this->info("Resetting password for: {$email}");
        
        // Find user
        $users = User::all();
        $foundUser = null;
        
        foreach ($users as $user) {
            if ($user->ms365_account === $email || $user->gmail_account === $email) {
                $foundUser = $user;
                break;
            }
        }
        
        if ($foundUser) {
            $oldHash = $foundUser->password;
            $foundUser->password = Hash::make($newPassword);
            $foundUser->save();
            
            $this->info("✓ Password reset successful!");
            $this->info("  - User ID: {$foundUser->id}");
            $this->info("  - Email: {$email}");
            $this->info("  - New Password: {$newPassword}");
            $this->info("  - Old Hash: " . substr($oldHash, 0, 50) . "...");
            $this->info("  - New Hash: " . substr($foundUser->password, 0, 50) . "...");
            
            // Verify the new password works
            if (Hash::check($newPassword, $foundUser->password)) {
                $this->info("✓ Password verification successful!");
            } else {
                $this->error("✗ Password verification failed after reset!");
            }
        } else {
            $this->error("✗ User not found with email: {$email}");
        }
    }
}
