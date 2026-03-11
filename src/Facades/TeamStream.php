<?php

namespace TeamStream\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool hasTeamFeatures()
 * @method static bool hasApiFeatures()
 * @method static bool hasProfilePhotoFeature()
 * @method static bool hasAccountDeletionFeature()
 * @method static bool hasTwoFactorAuthenticationFeature()
 * @method static bool hasEmailVerification()
 * @method static bool hasTeamInvitations()
 * @method static \TeamStream\TeamStream permissions(array $permissions)
 * @method static \TeamStream\TeamStream defaultApiTokenPermissions(array $permissions)
 * @method static array getPermissions()
 * @method static array getDefaultPermissions()
 * @method static \TeamStream\TeamStream role(string $key, string $name, array $permissions, string $description = '')
 * @method static array getRoles()
 * @method static array|null findRole(string $key)
 *
 * @see \TeamStream\TeamStream
 */
class TeamStream extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TeamStream\TeamStream::class;
    }
}
