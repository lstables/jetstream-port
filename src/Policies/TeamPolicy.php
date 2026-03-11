<?php

namespace LaravelStream\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    public function view(mixed $user, mixed $team): bool
    {
        return $user->belongsToTeam($team);
    }

    public function create(mixed $user): bool
    {
        return true;
    }

    public function update(mixed $user, mixed $team): bool
    {
        return $user->ownsTeam($team);
    }

    public function addTeamMember(mixed $user, mixed $team): bool
    {
        return $user->ownsTeam($team);
    }

    public function updateTeamMember(mixed $user, mixed $team): bool
    {
        return $user->ownsTeam($team);
    }

    public function removeTeamMember(mixed $user, mixed $team): bool
    {
        return $user->ownsTeam($team);
    }

    public function delete(mixed $user, mixed $team): bool
    {
        return $user->ownsTeam($team);
    }
}
