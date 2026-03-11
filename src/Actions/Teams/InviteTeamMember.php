<?php

namespace TeamStream\Actions\Teams;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use TeamStream\Contracts\InvitesTeamMembers;
use TeamStream\Mail\TeamInvitation;

class InviteTeamMember implements InvitesTeamMembers
{
    public function invite(mixed $user, mixed $team, string $email, ?string $role = null): void
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        Validator::make(['email' => $email, 'role' => $role], [
            'email' => [
                'required',
                'email',
                Rule::unique('team_invitations')->where(fn ($q) => $q->where('team_id', $team->id)),
            ],
            'role' => ['nullable', 'string', Rule::in(array_keys(app(\TeamStream\TeamStream::class)->getRoles()))],
        ], [
            'email.unique' => __('This user has already been invited to the team.'),
        ])->validateWithBag('addTeamMember');

        if ($team->hasUserWithEmail($email)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => [__('This user is already a member of the team.')],
            ])->errorBag('addTeamMember');
        }

        $invitation = config('TeamStream.models.team_invitation')::create([
            'team_id' => $team->id,
            'email' => $email,
            'role' => $role,
        ]);

        Mail::to($email)->send(new TeamInvitation($invitation));
    }
}
