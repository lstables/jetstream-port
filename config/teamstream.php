<?php

use TeamStream\Feature;

return [

    /*
    |--------------------------------------------------------------------------
    | TeamStream Stack
    |--------------------------------------------------------------------------
    |
    | This configuration value informs TeamStream which "stack" you will
    | be using with your Inertia application. Supported: "vue", "react".
    |
    */

    'stack' => env('TEAMSTREAM_STACK', 'vue'),

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Some of TeamStream's features are optional. You may disable them here.
    | Simply remove the feature from the array to disable it.
    |
    */

    'features' => [
        Feature::ProfilePhotos,
        Feature::Api,
        Feature::Teams,
        Feature::AccountDeletion,
        Feature::TwoFactorAuthentication,
        Feature::EmailVerification,
        Feature::TeamInvitations,
    ],

    /*
    |--------------------------------------------------------------------------
    | Profile Photo Disk
    |--------------------------------------------------------------------------
    |
    | The disk where profile photos will be stored. Defaults to "public".
    | When running in a cloud environment like Laravel Vapor, set to "s3".
    |
    */

    'profile_photo_disk' => env('TEAMSTREAM_PHOTO_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to all TeamStream routes.
    |
    */

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Team Model
    |--------------------------------------------------------------------------
    |
    | The model class that represents a "team" in your application. You may
    | change this to a custom model if you need to extend the defaults.
    |
    */

    'models' => [
        'team' => App\Models\Team::class,
        'user' => App\Models\User::class,
        'membership' => TeamStream\Models\Membership::class,
        'team_invitation' => TeamStream\Models\TeamInvitation::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Two Factor Authentication
    |--------------------------------------------------------------------------
    |
    | Settings for the built-in two-factor authentication feature.
    |
    */

    'two_factor' => [
        'window' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Token Permissions
    |--------------------------------------------------------------------------
    |
    | Define all possible permissions for your API tokens here. These are
    | configured in your app's TeamStreamServiceProvider (published on install).
    |
    */

    'api_token_permissions' => [
        'read',
        'create',
        'update',
        'delete',
    ],

];
