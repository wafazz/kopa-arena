<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_has_all_permissions(): void
    {
        $user = User::factory()->superadmin()->create();

        $this->assertTrue($user->hasPermission('manage_bookings'));
        $this->assertTrue($user->hasPermission('manage_staff'));
        $this->assertTrue($user->hasPermission('view_activity_logs'));
    }

    public function test_branch_manager_has_all_permissions(): void
    {
        $user = User::factory()->branchManager()->create();

        $this->assertTrue($user->hasPermission('manage_bookings'));
        $this->assertTrue($user->hasPermission('manage_staff'));
        $this->assertTrue($user->hasPermission('view_reports'));
    }

    public function test_hq_staff_only_has_assigned_permissions(): void
    {
        $user = User::factory()->hqStaff()->withPermissions(['manage_bookings'])->create();

        $this->assertTrue($user->hasPermission('manage_bookings'));
        $this->assertFalse($user->hasPermission('manage_staff'));
    }

    public function test_branch_staff_without_permissions_denied(): void
    {
        $user = User::factory()->branchStaff()->create(['permissions' => []]);

        $this->assertFalse($user->hasPermission('manage_bookings'));
        $this->assertFalse($user->hasPermission('view_activity_logs'));
    }

    public function test_has_role_with_string(): void
    {
        $user = User::factory()->superadmin()->create();

        $this->assertTrue($user->hasRole('superadmin'));
        $this->assertFalse($user->hasRole('hq_staff'));
    }

    public function test_has_role_with_comma_separated(): void
    {
        $user = User::factory()->hqStaff()->create();

        $this->assertTrue($user->hasRole('superadmin,hq_staff'));
        $this->assertFalse($user->hasRole('branch_manager,branch_staff'));
    }

    public function test_is_super_admin_check(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $hqStaff = User::factory()->hqStaff()->create();

        $this->assertTrue($superadmin->isSuperAdmin());
        $this->assertFalse($hqStaff->isSuperAdmin());
    }

    public function test_is_branch_staff_includes_both_branch_roles(): void
    {
        $manager = User::factory()->branchManager()->create();
        $staff = User::factory()->branchStaff()->create();
        $hq = User::factory()->hqStaff()->create();

        $this->assertTrue($manager->isBranchStaff());
        $this->assertTrue($staff->isBranchStaff());
        $this->assertFalse($hq->isBranchStaff());
    }
}
