<?php

namespace TeamStream\Contracts;

interface AddsTeamMembers
{
    public function add(mixed $user, mixed $team, string $email, ?string $role = null): void;
}
