<?php

namespace TeamStream\Actions\Teams;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use TeamStream\Contracts\CreatesTeams;

class CreateTeam implements CreatesTeams
{
    public function create(mixed $user, array $input): mixed
    {
        Gate::forUser($user)->authorize('create', config('TeamStream.models.team'));

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createTeam');

        $team = config('TeamStream.models.team')::forceCreate([
            'user_id' => $user->id,
            'name' => $input['name'],
            'personal_team' => false,
        ]);

        $user->switchTeam($team);

        return $team;
    }
}
