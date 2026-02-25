<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_hq_index_requires_auth(): void
    {
        $response = $this->get('/activity-logs');
        $response->assertRedirect('/login');
    }

    public function test_hq_index_requires_view_activity_logs_permission(): void
    {
        $user = User::factory()->hqStaff()->create();
        $response = $this->actingAs($user)->get('/activity-logs');
        $response->assertStatus(403);
    }

    public function test_superadmin_can_access_activity_logs(): void
    {
        $user = User::factory()->superadmin()->create();
        $response = $this->actingAs($user)->get('/activity-logs');
        $response->assertStatus(200);
    }

    public function test_hq_staff_with_permission_can_access(): void
    {
        $user = User::factory()->hqStaff()->withPermissions(['view_activity_logs'])->create();
        $response = $this->actingAs($user)->get('/activity-logs');
        $response->assertStatus(200);
    }

    public function test_hq_shows_all_logs_from_all_branches(): void
    {
        $user = User::factory()->superadmin()->create();
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();
        $user1 = User::factory()->branchStaff()->create(['branch_id' => $branch1->id]);
        $user2 = User::factory()->branchStaff()->create(['branch_id' => $branch2->id]);
        ActivityLog::factory()->create(['user_id' => $user1->id, 'action' => 'store']);
        ActivityLog::factory()->create(['user_id' => $user2->id, 'action' => 'update']);

        $response = $this->actingAs($user)->get('/activity-logs');
        $response->assertStatus(200);
        $response->assertViewHas('logs', fn ($logs) => $logs->total() >= 2);
    }

    public function test_filter_by_user_id(): void
    {
        $admin = User::factory()->superadmin()->create();
        $target = User::factory()->create();
        $other = User::factory()->create();
        ActivityLog::factory()->create(['user_id' => $target->id, 'action' => 'store']);
        ActivityLog::factory()->create(['user_id' => $other->id, 'action' => 'update']);

        $response = $this->actingAs($admin)->get('/activity-logs?user_id=' . $target->id);
        $response->assertStatus(200);
        $response->assertViewHas('logs', fn ($logs) => $logs->total() === 1);
    }

    public function test_filter_by_action(): void
    {
        $admin = User::factory()->superadmin()->create();
        $user = User::factory()->create();
        ActivityLog::factory()->create(['user_id' => $user->id, 'action' => 'login']);
        ActivityLog::factory()->create(['user_id' => $user->id, 'action' => 'store']);

        $response = $this->actingAs($admin)->get('/activity-logs?action=login');
        $response->assertStatus(200);
        $response->assertViewHas('logs', fn ($logs) => $logs->total() === 1);
    }

    public function test_filter_by_date_range(): void
    {
        $admin = User::factory()->superadmin()->create();
        $user = User::factory()->create();
        ActivityLog::factory()->create([
            'user_id' => $user->id,
            'action' => 'store',
            'created_at' => '2026-01-15 10:00:00',
        ]);
        ActivityLog::factory()->create([
            'user_id' => $user->id,
            'action' => 'update',
            'created_at' => '2026-02-15 10:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/activity-logs?date_from=2026-02-01&date_to=2026-02-28');
        $response->assertStatus(200);
        $response->assertViewHas('logs', fn ($logs) => $logs->total() === 1);
    }

    public function test_branch_scoped_to_branch_staff_only(): void
    {
        $branch = Branch::factory()->create();
        $branchUser = User::factory()->branchManager()->create(['branch_id' => $branch->id]);
        $otherUser = User::factory()->create();

        ActivityLog::factory()->create(['user_id' => $branchUser->id, 'action' => 'store']);
        ActivityLog::factory()->create(['user_id' => $otherUser->id, 'action' => 'update']);

        $response = $this->actingAs($branchUser)->get('/branch/activity-logs');
        $response->assertStatus(200);
        $response->assertViewHas('logs', fn ($logs) => $logs->total() === 1);
    }

    public function test_branch_requires_permission(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->branchStaff()->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get('/branch/activity-logs');
        $response->assertStatus(403);
    }
}
