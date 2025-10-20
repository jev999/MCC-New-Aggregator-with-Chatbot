<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Services\DataEncryptionService;
use App\Services\DataPurgingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityImplementationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_data_encryption_service_works()
    {
        $originalData = 'test@example.com';
        $encrypted = DataEncryptionService::encrypt($originalData);
        $decrypted = DataEncryptionService::decrypt($encrypted);
        
        $this->assertNotEquals($originalData, $encrypted);
        $this->assertEquals($originalData, $decrypted);
    }

    /** @test */
    public function test_user_model_encrypts_sensitive_fields()
    {
        $user = User::create([
            'first_name' => 'John',
            'surname' => 'Doe',
            'ms365_account' => 'john.doe@student.mcc-nac.edu.ph',
            'gmail_account' => 'johndoe@gmail.com',
            'password' => 'password123',
            'role' => 'student',
            'department' => 'BSIT',
            'year_level' => '1st Year',
        ]);

        // Check that sensitive fields are encrypted in database
        $rawUser = \DB::table('users')->where('id', $user->id)->first();
        $this->assertNotEquals('john.doe@student.mcc-nac.edu.ph', $rawUser->ms365_account);
        $this->assertNotEquals('johndoe@gmail.com', $rawUser->gmail_account);

        // Check that fields are decrypted when accessed
        $this->assertEquals('john.doe@student.mcc-nac.edu.ph', $user->ms365_account);
        $this->assertEquals('johndoe@gmail.com', $user->gmail_account);
    }

    /** @test */
    public function test_admin_model_encrypts_sensitive_fields()
    {
        $admin = Admin::create([
            'username' => 'admin123',
            'password' => 'password123',
            'role' => 'superadmin',
        ]);

        // Check that sensitive fields are encrypted in database
        $rawAdmin = \DB::table('admins')->where('id', $admin->id)->first();
        $this->assertNotEquals('admin123', $rawAdmin->username);

        // Check that fields are decrypted when accessed
        $this->assertEquals('admin123', $admin->username);
    }

    /** @test */
    public function test_data_purging_service_returns_stats()
    {
        $stats = DataPurgingService::getRetentionStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('total_admins', $stats);
        $this->assertArrayHasKey('total_notifications', $stats);
        $this->assertArrayHasKey('total_comments', $stats);
    }

    /** @test */
    public function test_legal_pages_are_accessible()
    {
        $this->get('/terms-and-conditions')
            ->assertStatus(200)
            ->assertSee('Terms and Conditions');

        $this->get('/privacy-policy')
            ->assertStatus(200)
            ->assertSee('Privacy Policy');
    }

    /** @test */
    public function test_security_headers_are_present()
    {
        $response = $this->get('/');
        
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }
}
