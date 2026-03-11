<?php

namespace TeamStream\Contracts;

interface DeletesTeams
{
    public function delete(mixed $team): void;
}
