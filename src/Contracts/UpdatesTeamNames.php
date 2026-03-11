<?php

namespace LaravelStream\Contracts;

interface UpdatesTeamNames
{
    public function update(mixed $team, array $input): void;
}
