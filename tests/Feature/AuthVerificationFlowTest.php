<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_created_user_is_verified_and_can_open_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-create@example.test',
            'password' => 'password',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Surveyor Baru',
                'email' => 'surveyor-baru@example.test',
                'password' => 'password123',
                'role' => 'surveyor',
            ])
            ->assertRedirect(route('users.index'));

        $createdUser = User::where('email', 'surveyor-baru@example.test')->firstOrFail();
        $this->assertNotNull($createdUser->email_verified_at);

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => 'surveyor-baru@example.test',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_authenticated_unverified_user_can_open_verify_email_page(): void
    {
        $user = User::create([
            'name' => 'Belum Verified',
            'email' => 'belum-verified@example.test',
            'password' => 'password',
            'role' => 'customer',
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $this->get(route('dashboard'))
            ->assertRedirect(route('otp.verify.form'));

        $this->get(route('otp.verify.form'))->assertOk();
    }
}
