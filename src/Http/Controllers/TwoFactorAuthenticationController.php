<?php

namespace TeamStream\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationController extends Controller
{
    public function __construct(protected Google2FA $google2fa)
    {
    }

    /**
     * Enable 2FA – generates secret but does NOT confirm yet.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! is_null($user->two_factor_secret)) {
            return back();
        }

        $user->forceFill([
            'two_factor_secret' => encrypt($this->google2fa->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, fn () =>
                Str::random(10).'-'.Str::random(10)
            )->all())),
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', '2fa-enabled-pending');
    }

    /**
     * Confirm 2FA with a TOTP code.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();

        $valid = $this->google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $request->code,
            config('TeamStream.two_factor.window', 1)
        );

        if (! $valid) {
            return back()->withErrors(['code' => [__('The provided two-factor authentication code was invalid.')]]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return back()->with('status', '2fa-confirmed');
    }

    /**
     * Disable 2FA.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', '2fa-disabled');
    }

    /**
     * Show recovery codes.
     */
    public function recoveryCodes(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'codes' => $request->user()->recoveryCodes(),
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, fn () =>
                Str::random(10).'-'.Str::random(10)
            )->all())),
        ])->save();

        return back()->with('status', 'recovery-codes-regenerated');
    }
}
