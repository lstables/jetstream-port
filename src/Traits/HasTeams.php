<?php

namespace LaravelStream\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use LaravelStream\LaravelStream;

trait HasTeams
{
    /**
     * Get all teams the user owns.
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(config('laravelstream.models.team'));
    }

    /**
     * Get all teams the user belongs to (not including owned).
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(
            config('laravelstream.models.team'),
            'team_user',
            'user_id',
            'team_id'
        )->withPivot('role')->withTimestamps()->as('membership');
    }

    /**
     * Get the user's current team.
     */
    public function currentTeam()
    {
        if (is_null($this->current_team_id) && $this->id) {
            $this->switchTeam($this->personalTeam());
        }

        return $this->belongsTo(config('laravelstream.models.team'), 'current_team_id')->first();
    }

    /**
     * Switch the user's current team.
     */
    public function switchTeam(mixed $team): bool
    {
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $this->forceFill(['current_team_id' => $team->id])->save();
        $this->setRelation('currentTeam', $team);

        return true;
    }

    /**
     * Get all teams including owned ones.
     */
    public function allTeams(): Collection
    {
        return $this->ownedTeams->merge($this->teams)->sortBy('name');
    }

    /**
     * Get the user's personal team.
     */
    public function personalTeam()
    {
        return $this->ownedTeams->where('personal_team', true)->first();
    }

    /**
     * Determine if the user owns the given team.
     */
    public function ownsTeam(mixed $team): bool
    {
        return (int) $this->id === (int) ($team->user_id ?? null);
    }

    /**
     * Determine if the user belongs to the given team.
     */
    public function belongsToTeam(mixed $team): bool
    {
        if (is_null($team)) {
            return false;
        }

        return $this->ownsTeam($team) || $this->teams->contains(fn ($t) => $t->id === $team->id);
    }

    /**
     * Get the role that the user has on the given team.
     */
    public function teamRole(mixed $team): ?array
    {
        if ($this->ownsTeam($team)) {
            return ['key' => 'owner', 'name' => 'Owner', 'description' => 'Team owner', 'permissions' => ['*']];
        }

        if (! $this->belongsToTeam($team)) {
            return null;
        }

        $membership = $this->teams
            ->where('id', $team->id)
            ->first()
            ?->membership;

        if (! $membership || ! $membership->role) {
            return null;
        }

        return app(LaravelStream::class)->findRole($membership->role);
    }

    /**
     * Determine if the user has the given role on the given team.
     */
    public function hasTeamRole(mixed $team, string $role): bool
    {
        if ($this->ownsTeam($team)) {
            return true;
        }

        return $this->belongsToTeam($team) && optional(
            app(LaravelStream::class)->findRole(
                $this->teams->where('id', $team->id)->first()?->membership?->role ?? ''
            )
        )['key'] === $role;
    }

    /**
     * Get the user's permissions for the given team.
     */
    public function teamPermissions(mixed $team): array
    {
        if ($this->ownsTeam($team)) {
            return ['*'];
        }

        if (! $this->belongsToTeam($team)) {
            return [];
        }

        return $this->teamRole($team)['permissions'] ?? [];
    }

    /**
     * Determine if the user has the given permission on the given team.
     */
    public function hasTeamPermission(mixed $team, string $permission): bool
    {
        if ($this->ownsTeam($team)) {
            return true;
        }

        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $permissions = $this->teamPermissions($team);

        return in_array('*', $permissions)
            || in_array($permission, $permissions)
            || (str_ends_with($permission, ':*') && in_array(substr($permission, 0, -2), $permissions));
    }
}
