<?php

namespace LaravelStream\Contracts;

interface DeletesUsers
{
    public function delete(mixed $user): void;
}
