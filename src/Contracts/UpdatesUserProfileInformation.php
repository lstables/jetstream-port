<?php

namespace TeamStream\Contracts;

interface UpdatesUserProfileInformation
{
    public function update(mixed $user, array $input): void;
}
