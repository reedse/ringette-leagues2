<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'managed_team_id',
        'provider',
        'provider_id',
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
            'password' => 'hashed',
        ];
    }

    /**
     * Get the roles associated with the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the player record associated with the user (if any).
     */
    public function player(): HasOne
    {
        return $this->hasOne(Player::class);
    }

    /**
     * Get the team managed by the user (if they are a coach/admin).
     */
    public function managedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'managed_team_id');
    }

    /**
     * Get the clips created by the user (coach).
     */
    public function createdClips(): HasMany
    {
        return $this->hasMany(Clip::class, 'coach_user_id');
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if the user has any of the specified roles.
     *
     * @param array $roleNames
     * @return bool
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if the user has all of the specified roles.
     *
     * @param array $roleNames
     * @return bool
     */
    public function hasAllRoles(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->count() === count($roleNames);
    }

    /**
     * Check if the user is a player.
     *
     * @return bool
     */
    public function isPlayer(): bool
    {
        return $this->hasRole('player');
    }

    /**
     * Check if the user is a coach/team admin.
     *
     * @return bool
     */
    public function isCoach(): bool
    {
        return $this->hasRole('coach');
    }

    /**
     * Check if the user is a league admin.
     *
     * @return bool
     */
    public function isLeagueAdmin(): bool
    {
        return $this->hasRole('league_admin');
    }
}
