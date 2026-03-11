<?php

namespace TeamStream\Contracts;

interface RemovesTeamMembers
{
    public function remove(mixed $user, mixed $team, mixed $teamMember): void;
}
