<?php

namespace LaravelStream\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use LaravelStream\Contracts\DeletesUsers;
use LaravelStream\Contracts\UpdatesUserPasswords;
use LaravelStream\Contracts\UpdatesUserProfileInformation;
use LaravelStream\LaravelStream;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $stack = config('laravelstream.stack', 'vue');
        $component = $stack === 'react' ? 'LaravelStream/Profile/Show' : 'LaravelStream/Profile/Show';

        return Inertia::render($component, [
            'sessions' => $this->sessions($request),
            'hasProfilePhoto' => LaravelStream::hasProfilePhotoFeature(),
            'hasTwoFactor' => LaravelStream::hasTwoFactorAuthenticationFeature(),
            'hasAccountDeletion' => LaravelStream::hasAccountDeletionFeature(),
            'twoFactorEnabled' => $request->user()->hasEnabledTwoFactorAuthentication(),
            'twoFactorPending' => $request->user()->hasPendingTwoFactorAuthentication(),
        ]);
    }

    public function update(Request $request, UpdatesUserProfileInformation $updater): RedirectResponse
    {
        $updater->update($request->user(), $request->all());

        return back()->with('status', 'profile-updated');
    }

    public function updatePassword(Request $request, UpdatesUserPasswords $updater): RedirectResponse
    {
        $updater->update($request->user(), $request->all());

        return back()->with('status', 'password-updated');
    }

    public function destroy(Request $request, DeletesUsers $deleter): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $deleter->delete($request->user());

        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $request->user()->deleteProfilePhoto();

        return back()->with('status', 'profile-photo-deleted');
    }

    protected function sessions(Request $request): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        return \DB::table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(fn ($session) => (object) [
                'agent' => $this->createAgent($session),
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $request->session()->getId(),
                'last_active' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ])
            ->all();
    }

    protected function createAgent(mixed $session): object
    {
        return (object) [
            'is_desktop' => true,
            'platform' => 'Unknown',
            'browser' => 'Unknown',
        ];
    }
}
