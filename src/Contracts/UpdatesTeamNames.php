<?php

namespace TeamStream\Contracts;

interface UpdatesTeamNames
{
    public function update(mixed $team, array $input): void;
}
