<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_creates_record_with_correct_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $log = ActivityLog::log('store', 'Booking', 5, 'Created booking');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'store',
            'model' => 'Booking',
            'model_id' => 5,
            'details' => 'Created booking',
        ]);
    }

    public function test_log_uses_authenticated_users_id(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $log = ActivityLog::log('login', 'User', $user->id);

        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_log_handles_optional_null_params(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $log = ActivityLog::log('login');

        $this->assertNull($log->model);
        $this->assertNull($log->model_id);
        $this->assertNull($log->details);
        $this->assertEquals('login', $log->action);
    }

    public function test_belongs_to_user_relationship(): void
    {
        $user = User::factory()->create();
        $log = ActivityLog::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    public function test_created_at_is_set_automatically(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $log = ActivityLog::log('test_action');

        $this->assertNotNull($log->created_at);
    }
}
