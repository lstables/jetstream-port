<?php

namespace TeamStream\Actions\Auth;

use TeamStream\Contracts\DeletesTeams;
use TeamStream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    public function __construct(protected DeletesTeams $deletesTeams)
    {
    }

    public function delete(mixed $user): void
    {
        if (method_exists($user, 'ownedTeams')) {
            $user->ownedTeams->each(fn ($team) => $this->deletesTeams->delete($team));
        }

        if (method_exists($user, 'deleteProfilePhoto')) {
            $user->deleteProfilePhoto();
        }

        $user->tokens()->delete();
        $user->delete();
    }
}
