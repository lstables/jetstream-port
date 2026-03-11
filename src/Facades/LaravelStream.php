<?php

namespace LaravelStream\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool hasTeamFeatures()
 * @method static bool hasApiFeatures()
 * @method static bool hasProfilePhotoFeature()
 * @method static bool hasAccountDeletionFeature()
 * @method static bool hasTwoFactorAuthenticationFeature()
 * @method static bool hasEmailVerification()
 * @method static bool hasTeamInvitations()
 * @method static \LaravelStream\LaravelStream permissions(array $permissions)
 * @method static \LaravelStream\LaravelStream defaultApiTokenPermissions(array $permissions)
 * @method static array getPermissions()
 * @method static array getDefaultPermissions()
 * @method static \LaravelStream\LaravelStream role(string $key, string $name, array $permissions, string $description = '')
 * @method static array getRoles()
 * @method static array|null findRole(string $key)
 *
 * @see \LaravelStream\LaravelStream
 */
class LaravelStream extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LaravelStream\LaravelStream::class;
    }
}
