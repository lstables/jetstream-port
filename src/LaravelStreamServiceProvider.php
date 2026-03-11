<?php

namespace LaravelStream;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use LaravelStream\Commands\InstallCommand;
use LaravelStream\Contracts\AddsTeamMembers;
use LaravelStream\Contracts\CreatesTeams;
use LaravelStream\Contracts\DeletesTeams;
use LaravelStream\Contracts\DeletesUsers;
use LaravelStream\Contracts\InvitesTeamMembers;
use LaravelStream\Contracts\RemovesTeamMembers;
use LaravelStream\Contracts\UpdatesTeamNames;
use LaravelStream\Contracts\UpdatesUserPasswords;
use LaravelStream\Contracts\UpdatesUserProfileInformation;
use LaravelStream\Actions\Auth\UpdateUserPassword;
use LaravelStream\Actions\Auth\UpdateUserProfileInformation;
use LaravelStream\Actions\Auth\DeleteUser;
use LaravelStream\Actions\Teams\AddTeamMember;
use LaravelStream\Actions\Teams\CreateTeam;
use LaravelStream\Actions\Teams\DeleteTeam;
use LaravelStream\Actions\Teams\InviteTeamMember;
use LaravelStream\Actions\Teams\RemoveTeamMember;
use LaravelStream\Actions\Teams\UpdateTeamName;

class LaravelStreamServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravelstream.php', 'laravelstream');

        $this->app->singleton(LaravelStream::class, fn () => new LaravelStream());

        // Bind action contracts to default implementations
        $this->app->singleton(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
        $this->app->singleton(UpdatesUserPasswords::class, UpdateUserPassword::class);
        $this->app->singleton(DeletesUsers::class, DeleteUser::class);
        $this->app->singleton(CreatesTeams::class, CreateTeam::class);
        $this->app->singleton(UpdatesTeamNames::class, UpdateTeamName::class);
        $this->app->singleton(AddsTeamMembers::class, AddTeamMember::class);
        $this->app->singleton(InvitesTeamMembers::class, InviteTeamMember::class);
        $this->app->singleton(RemovesTeamMembers::class, RemoveTeamMember::class);
        $this->app->singleton(DeletesTeams::class, DeleteTeam::class);
    }

    public function boot(): void
    {
        $this->configurePublishing();
        $this->configureCommands();
        $this->configureRoutes();
    }

    protected function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravelstream.php' => config_path('laravelstream.php'),
        ], 'laravelstream-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'laravelstream-migrations');

        $this->publishes([
            __DIR__.'/../stubs/inertia-vue' => resource_path('js'),
        ], 'laravelstream-vue');

        $this->publishes([
            __DIR__.'/../stubs/inertia-react' => resource_path('js'),
        ], 'laravelstream-react');

        $this->publishes([
            __DIR__.'/../routes/laravelstream.php' => base_path('routes/laravelstream.php'),
        ], 'laravelstream-routes');

        $this->publishes([
            __DIR__.'/../stubs/Actions' => app_path('Actions/LaravelStream'),
        ], 'laravelstream-actions');
    }

    protected function configureCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
        ]);
    }

    protected function configureRoutes(): void
    {
        if (file_exists(base_path('routes/laravelstream.php'))) {
            Route::middleware(config('laravelstream.middleware', ['web', 'auth']))
                ->group(base_path('routes/laravelstream.php'));
            return;
        }

        Route::middleware(config('laravelstream.middleware', ['web', 'auth']))
            ->group(__DIR__.'/../routes/laravelstream.php');
    }
}
