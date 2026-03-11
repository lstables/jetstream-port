<?php

namespace LaravelStream\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use LaravelStream\Contracts\AddsTeamMembers;
use LaravelStream\Contracts\CreatesTeams;
use LaravelStream\Contracts\DeletesTeams;
use LaravelStream\Contracts\InvitesTeamMembers;
use LaravelStream\Contracts\RemovesTeamMembers;
use LaravelStream\Contracts\UpdatesTeamNames;
use LaravelStream\LaravelStream;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('LaravelStream/Teams/Index', [
            'teams' => $request->user()->allTeams()->map->only(['id', 'name', 'personal_team']),
            'currentTeam' => $request->user()->currentTeam(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('LaravelStream/Teams/Create');
    }

    public function store(Request $request, CreatesTeams $creator): RedirectResponse
    {
        $team = $creator->create($request->user(), $request->all());

        return redirect()->route('teams.show', $team)->with('status', 'team-created');
    }

    public function show(Request $request, mixed $team): Response
    {
        $teamModel = config('laravelstream.models.team')::findOrFail($team);

        $this->authorize('view', $teamModel);

        return Inertia::render('LaravelStream/Teams/Show', [
            'team' => $teamModel->load('owner', 'users'),
            'availableRoles' => array_values(app(LaravelStream::class)->getRoles()),
            'userPermissions' => [
                'canAddTeamMembers' => $request->user()->can('addTeamMember', $teamModel),
                'canDeleteTeam' => $request->user()->can('delete', $teamModel),
                'canRemoveTeamMembers' => $request->user()->can('removeTeamMember', $teamModel),
                'canUpdateTeam' => $request->user()->can('update', $teamModel),
            ],
        ]);
    }

    public function update(Request $request, mixed $team, UpdatesTeamNames $updater): RedirectResponse
    {
        $teamModel = config('laravelstream.models.team')::findOrFail($team);
        $updater->update($teamModel, $request->all());

        return back()->with('status', 'team-updated');
    }

    public function destroy(Request $request, mixed $team, DeletesTeams $deleter): RedirectResponse
    {
        $teamModel = config('laravelstream.models.team')::findOrFail($team);

        $this->authorize('delete', $teamModel);

        $deleter->delete($teamModel);

        return redirect()->route('dashboard')->with('status', 'team-deleted');
    }

    public function addMember(Request $request, mixed $team, InvitesTeamMembers $inviter): RedirectResponse
    {
        $teamModel = config('laravelstream.models.team')::findOrFail($team);

        if (LaravelStream::hasTeamInvitations()) {
            app(InvitesTeamMembers::class)->invite($request->user(), $teamModel, $request->email, $request->role);
        } else {
            app(AddsTeamMembers::class)->add($request->user(), $teamModel, $request->email, $request->role);
        }

        return back()->with('status', 'team-member-added');
    }

    public function removeMember(Request $request, mixed $team, mixed $user): RedirectResponse
    {
        $teamModel = config('laravelstream.models.team')::findOrFail($team);
        $member = config('laravelstream.models.user')::findOrFail($user);

        app(RemovesTeamMembers::class)->remove($request->user(), $teamModel, $member);

        return back()->with('status', 'team-member-removed');
    }

    public function switchTeam(Request $request, mixed $team): RedirectResponse
    {
        $teamModel = config('laravelstream.models.team')::findOrFail($team);

        if (! $request->user()->switchTeam($teamModel)) {
            abort(403);
        }

        return redirect(config('app.url'), 303);
    }

    public function cancelInvitation(Request $request, mixed $invitationId): RedirectResponse
    {
        $invitation = config('laravelstream.models.team_invitation')::findOrFail($invitationId);

        $this->authorize('removeTeamMember', $invitation->team);

        $invitation->delete();

        return back()->with('status', 'invitation-cancelled');
    }

    public function acceptInvitation(Request $request, string $token): RedirectResponse
    {
        $invitation = config('laravelstream.models.team_invitation')::where('token', $token)->firstOrFail();

        app(AddsTeamMembers::class)->add(
            $invitation->team->owner,
            $invitation->team,
            $request->user()->email,
            $invitation->role,
        );

        $invitation->delete();

        return redirect()->route('teams.show', $invitation->team)->with('status', 'invitation-accepted');
    }
}
