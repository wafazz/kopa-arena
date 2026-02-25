<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_creates_activity_log(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'login',
            'model' => 'User',
        ]);
    }

    public function test_login_updates_last_login_at(): void
    {
        $user = User::factory()->create(['last_login_at' => null]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    public function test_login_updates_last_login_ip(): void
    {
        $user = User::factory()->create(['last_login_ip' => null]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_ip);
    }

    public function test_login_log_details_contain_ip(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'login',
        ]);

        $log = \App\Models\ActivityLog::where('user_id', $user->id)->where('action', 'login')->first();
        $this->assertStringContains('IP:', $log->details);
    }

    public function test_logout_creates_activity_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'logout',
            'model' => 'User',
        ]);
    }

    public function test_logout_log_has_correct_user_id(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $log = \App\Models\ActivityLog::where('action', 'logout')->first();
        $this->assertEquals($user->id, $log->user_id);
    }

    protected function assertStringContains(string $needle, ?string $haystack): void
    {
        $this->assertNotNull($haystack);
        $this->assertTrue(str_contains($haystack, $needle), "Failed asserting that '$haystack' contains '$needle'.");
    }
}
