<?php

namespace LaravelStream\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Team extends Model
{
    protected $fillable = ['name', 'user_id', 'personal_team'];

    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * The owner of the team.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(config('laravelstream.models.user'), 'user_id');
    }

    /**
     * All users belonging to the team (including the owner).
     */
    public function allUsers(): Collection
    {
        return $this->users->merge([$this->owner]);
    }

    /**
     * All non-owner members of the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('laravelstream.models.user'),
            'team_user',
            'team_id',
            'user_id'
        )->withPivot('role')->withTimestamps()->as('membership');
    }

    /**
     * Get all pending team invitations.
     */
    public function teamInvitations(): HasMany
    {
        return $this->hasMany(config('laravelstream.models.team_invitation'));
    }

    /**
     * Determine if the given user belongs to the team.
     */
    public function hasUser(mixed $user): bool
    {
        return $this->users->contains($user) || $user->ownsTeam($this);
    }

    /**
     * Determine if the given user is a member (not owner).
     */
    public function hasMember(mixed $user): bool
    {
        return $this->users->contains($user);
    }

    /**
     * Determine if the team has a member with the given email.
     */
    public function hasUserWithEmail(string $email): bool
    {
        return $this->allUsers()->contains(fn ($u) => $u->email === $email);
    }

    /**
     * Determine if the given user has the given permission on the team.
     */
    public function userHasPermission(mixed $user, string $permission): bool
    {
        return $user->hasTeamPermission($this, $permission);
    }

    /**
     * Remove the given user from the team.
     */
    public function removeUser(mixed $user): void
    {
        if ($user->current_team_id === $this->id) {
            $user->forceFill(['current_team_id' => null])->save();
        }

        $this->users()->detach($user);
    }

    /**
     * Purge all the team's resources including its users.
     */
    public function purge(): void
    {
        $this->owner()->where('current_team_id', $this->id)
            ->update(['current_team_id' => null]);

        $this->users()->where('current_team_id', $this->id)
            ->update(['current_team_id' => null]);

        $this->users()->detach();
        $this->delete();
    }
}
