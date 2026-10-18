<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'address', 'city', 'blocked_at', 'blocked_reason', 'email_verified_at', 'phone_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * The Role row backing this account: admins map to the built-in
     * 'admin' slug, staff to a custom row, customer to nothing.
     */
    public function assignedRole()
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    /**
     * Can this account manage the given admin-panel section?
     * Admin accounts may always; other staff roles need the permission.
     * Plain customers have no panel access at all.
     */
    public function canManage(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $role = $this->assignedRole()->first();

        return $role !== null && $role->permissions()->where('key', $permission)->exists();
    }

    public function hasPanelAccess(): bool
    {
        return $this->isAdmin() || $this->assignedRole()->exists();
    }

    /**
     * A customer account is usable once it passed the registration OTP
     * check (or the OTP requirement is disabled). Admins skip it.
     */
    public function isVerified(): bool
    {
        return $this->isAdmin()
            || $this->email_verified_at !== null
            || $this->phone_verified_at !== null;
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function measurementProfiles()
    {
        return $this->hasMany(MeasurementProfile::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'blocked_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
