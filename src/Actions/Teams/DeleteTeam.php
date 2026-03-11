<?php

namespace TeamStream\Actions\Teams;

use TeamStream\Contracts\DeletesTeams;

class DeleteTeam implements DeletesTeams
{
    public function delete(mixed $team): void
    {
        $team->purge();
    }
}
