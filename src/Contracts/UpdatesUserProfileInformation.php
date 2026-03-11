<?php

namespace LaravelStream\Contracts;

interface UpdatesUserProfileInformation
{
    public function update(mixed $user, array $input): void;
}
