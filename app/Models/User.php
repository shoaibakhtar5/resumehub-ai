<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_path',
        'phone',
        'is_admin',
        'status',
        'timezone',
        'locale',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('assigned_by_user_id')
            ->withTimestamps();
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'created_by_user_id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->is_admin || $this->roles()->where('name', $role)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
            ->exists();
    }

    public function assignRole(string $role): void
    {
        $roleModel = Role::firstOrCreate(
            ['name' => $role],
            ['guard_name' => 'web', 'description' => ucfirst($role), 'is_system' => true]
        );

        $this->roles()->syncWithoutDetaching([$roleModel->id]);
    }
}
