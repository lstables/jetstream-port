<?php

namespace LaravelStream\Contracts;

interface DeletesTeams
{
    public function delete(mixed $team): void;
}
