<?php

namespace LaravelStream\Actions\Teams;

use LaravelStream\Contracts\DeletesTeams;

class DeleteTeam implements DeletesTeams
{
    public function delete(mixed $team): void
    {
        $team->purge();
    }
}
