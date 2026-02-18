<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'branch_id', 'is_active', 'permissions', 'profile_image',
        'last_login_at', 'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    public function isHqStaff()
    {
        return $this->role === 'hq_staff';
    }

    public function isBranchStaff()
    {
        return in_array($this->role, ['branch_staff', 'branch_manager']);
    }

    public function isBranchManager()
    {
        return $this->role === 'branch_manager';
    }

    public function hasPermission($permission)
    {
        if ($this->isSuperAdmin() || $this->isBranchManager()) {
            return true;
        }
        $perms = $this->permissions ?? [];
        return in_array($permission, $perms);
    }

    public function hasRole($roles)
    {
        if (is_string($roles)) {
            $roles = explode(',', $roles);
        }
        return in_array($this->role, $roles);
    }
}
