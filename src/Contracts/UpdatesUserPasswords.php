<?php

namespace LaravelStream\Contracts;

interface UpdatesUserPasswords
{
    public function update(mixed $user, array $input): void;
}
