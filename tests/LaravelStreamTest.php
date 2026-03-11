<?php

use LaravelStream\LaravelStream;
use LaravelStream\Feature;

it('can check for team features', function () {
    config(['laravelstream.features' => [Feature::Teams]]);
    expect(LaravelStream::hasTeamFeatures())->toBeTrue();
});

it('can check features are disabled', function () {
    config(['laravelstream.features' => []]);
    expect(LaravelStream::hasTeamFeatures())->toBeFalse();
    expect(LaravelStream::hasApiFeatures())->toBeFalse();
});

it('can register roles', function () {
    $stream = new LaravelStream();
    $stream->role('editor', 'Editor', ['read', 'create'], 'Can edit content');

    $role = $stream->findRole('editor');

    expect($role)->not->toBeNull()
        ->and($role['name'])->toBe('Editor')
        ->and($role['permissions'])->toBe(['read', 'create']);
});

it('can set permissions', function () {
    $stream = new LaravelStream();
    $stream->permissions(['read', 'write', 'delete']);
    $stream->defaultApiTokenPermissions(['read']);

    expect($stream->getPermissions())->toBe(['read', 'write', 'delete']);
    expect($stream->getDefaultPermissions())->toBe(['read']);
});
