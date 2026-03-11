<?php

namespace TeamStream;

class TeamStream
{
    /** @var array<string, array{name: string, key: string, description: string}> */
    protected array $roles = [];

    /** @var string[] */
    protected array $permissions = [];

    /** @var string[] */
    protected array $defaultPermissions = [];

    /** @var array<string, callable> */
    protected array $findTeamByIdResolver = [];

    /**
     * Determine if the given feature is enabled.
     */
    public static function hasTeamFeatures(): bool
    {
        return in_array(Feature::Teams, config('teamstream.features', []));
    }

    public static function hasApiFeatures(): bool
    {
        return in_array(Feature::Api, config('teamstream.features', []));
    }

    public static function hasProfilePhotoFeature(): bool
    {
        return in_array(Feature::ProfilePhotos, config('teamstream.features', []));
    }

    public static function hasAccountDeletionFeature(): bool
    {
        return in_array(Feature::AccountDeletion, config('teamstream.features', []));
    }

    public static function hasTwoFactorAuthenticationFeature(): bool
    {
        return in_array(Feature::TwoFactorAuthentication, config('teamstream.features', []));
    }

    public static function hasEmailVerification(): bool
    {
        return in_array(Feature::EmailVerification, config('teamstream.features', []));
    }

    public static function hasTeamInvitations(): bool
    {
        return in_array(Feature::TeamInvitations, config('teamstream.features', []));
    }

    /**
     * Define the available API token permissions.
     */
    public function permissions(array $permissions): static
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Define the default permissions for new API tokens.
     */
    public function defaultApiTokenPermissions(array $permissions): static
    {
        $this->defaultPermissions = $permissions;

        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getDefaultPermissions(): array
    {
        return $this->defaultPermissions;
    }

    /**
     * Define the roles available within the application.
     */
    public function role(string $key, string $name, array $permissions, string $description = ''): static
    {
        $this->roles[$key] = [
            'key' => $key,
            'name' => $name,
            'permissions' => $permissions,
            'description' => $description,
        ];

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function findRole(string $key): ?array
    {
        return $this->roles[$key] ?? null;
    }
}
