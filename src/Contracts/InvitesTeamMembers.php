<?php

namespace TeamStream\Contracts;

interface InvitesTeamMembers
{
    public function invite(mixed $user, mixed $team, string $email, ?string $role = null): void;
}
