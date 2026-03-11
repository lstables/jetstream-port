<?php

namespace TeamStream\Actions\Teams;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use TeamStream\Contracts\AddsTeamMembers;

class AddTeamMember implements AddsTeamMembers
{
    public function add(mixed $user, mixed $team, string $email, ?string $role = null): void
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $newMember = config('TeamStream.models.user')::where('email', $email)->firstOrFail();

        Validator::make(['email' => $email, 'role' => $role], [
            'email' => ['required', 'email', Rule::exists('users', 'email')],
            'role' => ['nullable', 'string', Rule::in(array_keys(app(\TeamStream\TeamStream::class)->getRoles()))],
        ])->validateWithBag('addTeamMember');

        if ($team->hasUser($newMember)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => [__('This user is already a member of the team.')],
            ])->errorBag('addTeamMember');
        }

        $team->users()->attach($newMember, ['role' => $role]);
    }
}
