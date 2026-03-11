<?php

namespace TeamStream\Contracts;

interface DeletesUsers
{
    public function delete(mixed $user): void;
}
