<?php

namespace LaravelStream\Contracts;

interface RemovesTeamMembers
{
    public function remove(mixed $user, mixed $team, mixed $teamMember): void;
}
