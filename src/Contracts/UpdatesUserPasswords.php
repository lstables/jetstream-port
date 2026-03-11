<?php

namespace TeamStream\Contracts;

interface UpdatesUserPasswords
{
    public function update(mixed $user, array $input): void;
}
