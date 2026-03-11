<?php

namespace LaravelStream\Contracts;

interface AddsTeamMembers
{
    public function add(mixed $user, mixed $team, string $email, ?string $role = null): void;
}
