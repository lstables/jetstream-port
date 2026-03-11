<?php

namespace TeamStream\Contracts;

interface CreatesTeams
{
    public function create(mixed $user, array $input): mixed;
}
