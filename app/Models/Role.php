<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role', 'slug');
    }

    /**
     * The Admin row is the built-in super-role and cannot be edited or deleted.
     */
    public function isBuiltIn(): bool
    {
        return $this->slug === 'admin';
    }
}
