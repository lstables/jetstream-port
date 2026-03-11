<?php

namespace LaravelStream\Contracts;

interface CreatesTeams
{
    public function create(mixed $user, array $input): mixed;
}
