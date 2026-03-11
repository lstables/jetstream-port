<?php

namespace LaravelStream\Actions\Teams;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use LaravelStream\Contracts\RemovesTeamMembers;

class RemoveTeamMember implements RemovesTeamMembers
{
    public function remove(mixed $user, mixed $team, mixed $teamMember): void
    {
        if ($user->id === $teamMember->id) {
            // User leaving the team themselves
        } else {
            if (! Gate::forUser($user)->check('removeTeamMember', $team)) {
                throw new AuthorizationException;
            }
        }

        if ($team->owner_id === $teamMember->id) {
            throw new AuthorizationException;
        }

        $team->removeUser($teamMember);
    }
}
