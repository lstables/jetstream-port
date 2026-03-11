<?php

use Illuminate\Support\Facades\Route;
use LaravelStream\Http\Controllers\ApiTokenController;
use LaravelStream\Http\Controllers\ProfileController;
use LaravelStream\Http\Controllers\TeamController;
use LaravelStream\Http\Controllers\TwoFactorAuthenticationController;
use LaravelStream\LaravelStream;

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::group([], function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/user/profile-information', [ProfileController::class, 'update'])->name('user-profile-information.update');
    Route::put('/user/password', [ProfileController::class, 'updatePassword'])->name('user-password.update');

    if (LaravelStream::hasProfilePhotoFeature()) {
        Route::delete('/user/profile-photo', [ProfileController::class, 'destroyPhoto'])->name('current-user-photo.destroy');
    }

    if (LaravelStream::hasAccountDeletionFeature()) {
        Route::delete('/user', [ProfileController::class, 'destroy'])->name('current-user.destroy');
    }
});

/*
|--------------------------------------------------------------------------
| Two Factor Authentication Routes
|--------------------------------------------------------------------------
*/
if (LaravelStream::hasTwoFactorAuthenticationFeature()) {
    Route::group([], function () {
        Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])->name('two-factor.enable');
        Route::post('/user/confirmed-two-factor-authentication', [TwoFactorAuthenticationController::class, 'confirm'])->name('two-factor.confirm');
        Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])->name('two-factor.disable');
        Route::get('/user/two-factor-recovery-codes', [TwoFactorAuthenticationController::class, 'recoveryCodes'])->name('two-factor.recovery-codes');
        Route::post('/user/two-factor-recovery-codes', [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes.regenerate');
    });
}

/*
|--------------------------------------------------------------------------
| API Token Routes
|--------------------------------------------------------------------------
*/
if (LaravelStream::hasApiFeatures()) {
    Route::group([], function () {
        Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
        Route::post('/user/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
        Route::put('/user/api-tokens/{tokenId}', [ApiTokenController::class, 'update'])->name('api-tokens.update');
        Route::delete('/user/api-tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
    });
}

/*
|--------------------------------------------------------------------------
| Team Routes
|--------------------------------------------------------------------------
*/
if (LaravelStream::hasTeamFeatures()) {
    Route::group([], function () {
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

        // Team membership
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('team-members.store');
        Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('team-members.destroy');

        // Switch active team
        Route::put('/current-team', [TeamController::class, 'switchTeam'])->name('current-team.update');

        // Invitations
        if (LaravelStream::hasTeamInvitations()) {
            Route::delete('/team-invitations/{invitation}', [TeamController::class, 'cancelInvitation'])->name('team-invitations.destroy');
            Route::get('/team-invitations/{token}/accept', [TeamController::class, 'acceptInvitation'])->name('team-invitations.accept');
        }
    });
}
