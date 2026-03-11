<?php

namespace LaravelStream\Traits;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

trait TwoFactorAuthenticatable
{
    /**
     * Determine if two-factor authentication has been enabled.
     */
    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    /**
     * Determine if two-factor authentication is enabled but not yet confirmed.
     */
    public function hasPendingTwoFactorAuthentication(): bool
    {
        return ! is_null($this->two_factor_secret) && is_null($this->two_factor_confirmed_at);
    }

    /**
     * Get the user's two-factor authentication QR code URL.
     */
    public function twoFactorQrCodeUrl(): string
    {
        return app(Google2FA::class)->getQRCodeUrl(
            config('app.name'),
            $this->email,
            decrypt($this->two_factor_secret)
        );
    }

    /**
     * Get the SVG element for the user's two-factor authentication QR code.
     */
    public function twoFactorQrCodeSvg(): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(192, 0, null, null, Fill::uniformColor(
                    new Rgb(255, 255, 255),
                    new Rgb(45, 55, 72)
                )),
                new SvgImageBackEnd
            )
        ))->writeString($this->twoFactorQrCodeUrl());

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    /**
     * Get the two-factor authentication setup key.
     */
    public function twoFactorSecretKey(): string
    {
        return decrypt($this->two_factor_secret);
    }

    /**
     * Get the valid two-factor authentication recovery codes.
     */
    public function recoveryCodes(): array
    {
        return json_decode(decrypt($this->two_factor_recovery_codes), true);
    }

    /**
     * Replace the given recovery code with a new one.
     */
    public function replaceRecoveryCode(string $code): void
    {
        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(str_replace(
                $code,
                Str::random(10).'-'.Str::random(10),
                decrypt($this->two_factor_recovery_codes)
            )),
        ])->save();
    }
}
