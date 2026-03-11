# TeamStream

**TeamStream** is a Jetstream-inspired feature package for **Laravel 11/12** with the new starter kits (Vue + Inertia 2, React + Inertia 2, TypeScript, shadcn/ui). It ports Jetstream's most-loved features — Team Management, API Tokens, Profile Management, and Two-Factor Authentication — without requiring you to swap your starter kit.

> **Why TeamStream?**
> Laravel 12 officially deprecated Jetstream in favour of new starter kits, but those kits ship without teams, API tokens, or 2FA. TeamStream fills that gap as a drop-in composer package.

---

## Features

| Feature | Description |
|---|---|
| 👤 **Profile Management** | Update name, email, and optionally profile photos |
| 🔑 **Password Management** | Secure password update with current-password confirmation |
| 🛡️ **Two-Factor Auth** | TOTP (Google Authenticator) with QR code + recovery codes |
| 🔒 **API Tokens** | Sanctum-powered tokens with granular per-token permissions |
| 👥 **Teams** | Multi-team support with roles, invitations, and switching |
| 📧 **Team Invitations** | Email-based invites (optional; falls back to direct add) |
| 🗑️ **Account Deletion** | Self-service account deletion |
| 🎨 **Vue + React** | Full UI stubs for both stacks using shadcn/ui components |

---

## Requirements

- PHP **8.2+**
- Laravel **11.x** or **12.x**
- An existing app using the Laravel 12 **Vue** or **React** starter kit (Inertia 2 + shadcn/ui)
- `laravel/sanctum` (pulled in automatically)

---

## Installation

```bash
composer require teamstream/teamstream
```

### Install for Vue (default)

```bash
php artisan teamstream:install vue --teams --api --2fa --photos
```

### Install for React

```bash
php artisan teamstream:install react --teams --api --2fa --photos
```

### Flags

| Flag | Description |
|---|---|
| `--teams` | Enable team management |
| `--api` | Enable API token management |
| `--2fa` | Enable two-factor authentication |
| `--photos` | Enable profile photo uploads |
| `--no-deletion` | Disable account deletion |

The installer will:

1. Publish the config file to `config/teamstream.php`
2. Publish database migrations
3. Publish Vue or React UI components into `resources/js/`
4. Publish the route file to `routes/teamstream.php`
5. Publish customisable action classes to `app/Actions/TeamStream/`
6. Create and register `app/Providers/TeamStreamServiceProvider.php`
7. Update your `App\Models\User` with the required traits
8. Optionally run migrations

---

## Configuration

After installing, your `config/teamstream.php` controls which features are active:

```php
use TeamStream\Feature;

return [
    'stack' => 'vue', // or 'react'

    'features' => [
        Feature::ProfilePhotos,
        Feature::Api,
        Feature::Teams,
        Feature::AccountDeletion,
        Feature::TwoFactorAuthentication,
        Feature::EmailVerification,
        Feature::TeamInvitations,
    ],

    'profile_photo_disk' => env('TEAMSTREAM_PHOTO_DISK', 'public'),
];
```

Simply remove a `Feature` from the array to disable it — no routes, no UI, no DB columns needed.

---

## User Model

The installer adds these traits to your `App\Models\User`:

```php
use TeamStream\Traits\HasTeams;
use TeamStream\Traits\HasProfilePhoto;
use TeamStream\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasTeams, HasProfilePhoto, TwoFactorAuthenticatable, Notifiable;
}
```

If you don't use all features, you can omit the corresponding traits.

---

## Customising Actions

All business logic lives in published action classes at `app/Actions/TeamStream/`. These are bound to contracts in your `TeamStreamServiceProvider`, making them trivial to swap:

```
app/Actions/TeamStream/
├── CreateTeam.php
├── UpdateTeamName.php
├── AddTeamMember.php
├── InviteTeamMember.php
├── RemoveTeamMember.php
├── DeleteTeam.php
├── UpdateUserProfileInformation.php
├── UpdateUserPassword.php
└── DeleteUser.php
```

Example — restrict team creation to users on a paid plan:

```php
// app/Actions/TeamStream/CreateTeam.php

public function create(mixed $user, array $input): mixed
{
    if (! $user->onPaidPlan()) {
        throw new \RuntimeException('Team creation requires a paid plan.');
    }

    // ... rest of the default logic
}
```

---

## Defining Roles & Permissions

In your published `app/Providers/TeamStreamServiceProvider.php`:

```php
public function boot(): void
{
    $this->app->make(TeamStream::class)
        ->permissions(['read', 'create', 'update', 'delete'])
        ->defaultApiTokenPermissions(['read'])
        ->role('admin', 'Administrator', ['*'], 'Full access to all team resources')
        ->role('editor', 'Editor', ['read', 'create', 'update'], 'Can view and edit resources')
        ->role('viewer', 'Viewer', ['read'], 'Can only view resources');
}
```

Roles are stored as a string on the `team_user` pivot. The `HasTeams` trait exposes:

```php
$user->hasTeamRole($team, 'editor');         // bool
$user->hasTeamPermission($team, 'create');   // bool
$user->teamPermissions($team);               // ['read', 'create', 'update']
$user->teamRole($team);                      // ['key' => 'editor', 'name' => 'Editor', ...]
```

---

## Routes

All routes are in the published `routes/TeamStream.php`. They are automatically required from `routes/web.php` and protected by `['web', 'auth']` middleware.

| Method | URI | Name |
|---|---|---|
| GET | `/profile` | `profile.show` |
| PUT | `/user/profile-information` | `user-profile-information.update` |
| PUT | `/user/password` | `user-password.update` |
| DELETE | `/user/profile-photo` | `current-user-photo.destroy` |
| DELETE | `/user` | `current-user.destroy` |
| POST | `/user/two-factor-authentication` | `two-factor.enable` |
| POST | `/user/confirmed-two-factor-authentication` | `two-factor.confirm` |
| DELETE | `/user/two-factor-authentication` | `two-factor.disable` |
| GET | `/user/api-tokens` | `api-tokens.index` |
| POST | `/user/api-tokens` | `api-tokens.store` |
| PUT | `/user/api-tokens/{id}` | `api-tokens.update` |
| DELETE | `/user/api-tokens/{id}` | `api-tokens.destroy` |
| GET | `/teams` | `teams.index` |
| POST | `/teams` | `teams.store` |
| GET | `/teams/{team}` | `teams.show` |
| PUT | `/teams/{team}` | `teams.update` |
| DELETE | `/teams/{team}` | `teams.destroy` |
| POST | `/teams/{team}/members` | `team-members.store` |
| DELETE | `/teams/{team}/members/{user}` | `team-members.destroy` |
| PUT | `/current-team` | `current-team.update` |

---

## Frontend Components

After install, components are published into `resources/js/components/TeamStream/` and pages into `resources/js/Pages/TeamStream/`.

### Vue (shadcn-vue)

```
resources/js/
├── Pages/TeamStream/
│   ├── Profile/Show.vue
│   ├── API/Index.vue
│   └── Teams/Show.vue, Create.vue
└── components/TeamStream/
    ├── Profile/
    │   ├── UpdateProfileInformationForm.vue
    │   ├── UpdatePasswordForm.vue
    │   ├── TwoFactorAuthenticationForm.vue
    │   └── DeleteUserForm.vue
    ├── API/
    │   ├── CreateApiTokenForm.vue
    │   └── ApiTokenList.vue
    └── Teams/
        ├── TeamNameForm.vue
        ├── TeamMemberManager.vue
        └── DeleteTeamForm.vue
```

### React (shadcn/ui)

Same structure but `.tsx` files. All components are typed with TypeScript and use the same shadcn/ui primitives shipped by the Laravel 12 React starter kit.

---

## Adding Nav Links

Add links to your existing nav component. Example for Vue:

```vue
<NavLink :href="route('profile.show')" :active="route().current('profile.show')">
  Profile
</NavLink>
<NavLink :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
  API Tokens
</NavLink>
<NavLink :href="route('teams.show', $page.props.auth.user.current_team?.id)"
  :active="route().current('teams.*')">
  Team Settings
</NavLink>
```

---

## Team Switcher

Add a team switcher to your top navigation. With the `HasTeams` trait, you can access:

```php
// In Inertia shared data (HandleInertiaRequests middleware)
'teams' => $user->allTeams()->map->only(['id', 'name', 'personal_team']),
'currentTeam' => $user->currentTeam()?->only(['id', 'name']),
```

Switching is a PUT to `/current-team` with `{ team: teamId }`.

---

## API Token Usage

Tokens created by users can be verified in your controllers/policies:

```php
// Check a specific permission
if ($request->user()->tokenCan('create')) {
    // ...
}

// Protect API routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('posts', PostController::class);
});
```

---

## Upgrading from Jetstream

| Jetstream | TeamStream |
|---|---|
| `Laravel\Jetstream\HasTeams` | `TeamStream\Traits\HasTeams` |
| `Laravel\Jetstream\HasProfilePhoto` | `TeamStream\Traits\HasProfilePhoto` |
| `Laravel\Jetstream\TwoFactorAuthenticatable` | `TeamStream\Traits\TwoFactorAuthenticatable` |
| `Jetstream::role(...)` | `TeamStream::role(...)` |
| `Jetstream::permissions(...)` | `TeamStream::permissions(...)` |
| `Jetstream::hasTeamFeatures()` | `TeamStream::hasTeamFeatures()` |
| `app/Actions/Jetstream/` | `app/Actions/TeamStream/` |

---

## Testing

```bash
composer test
# or
./vendor/bin/pest
```

---

## License

MIT
