<?php

namespace TeamStream;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use TeamStream\Commands\InstallCommand;
use TeamStream\Contracts\AddsTeamMembers;
use TeamStream\Contracts\CreatesTeams;
use TeamStream\Contracts\DeletesTeams;
use TeamStream\Contracts\DeletesUsers;
use TeamStream\Contracts\InvitesTeamMembers;
use TeamStream\Contracts\RemovesTeamMembers;
use TeamStream\Contracts\UpdatesTeamNames;
use TeamStream\Contracts\UpdatesUserPasswords;
use TeamStream\Contracts\UpdatesUserProfileInformation;
use TeamStream\Actions\Auth\UpdateUserPassword;
use TeamStream\Actions\Auth\UpdateUserProfileInformation;
use TeamStream\Actions\Auth\DeleteUser;
use TeamStream\Actions\Teams\AddTeamMember;
use TeamStream\Actions\Teams\CreateTeam;
use TeamStream\Actions\Teams\DeleteTeam;
use TeamStream\Actions\Teams\InviteTeamMember;
use TeamStream\Actions\Teams\RemoveTeamMember;
use TeamStream\Actions\Teams\UpdateTeamName;

class TeamStreamServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/teamstream.php', 'teamstream');

        $this->app->singleton(TeamStream::class, fn () => new TeamStream());

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
            __DIR__.'/../config/teamstream.php' => config_path('teamstream.php'),
        ], 'teamstream-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'teamstream-migrations');

        $this->publishes([
            __DIR__.'/../stubs/inertia-vue' => resource_path('js'),
        ], 'teamstream-vue');

        $this->publishes([
            __DIR__.'/../stubs/inertia-react' => resource_path('js'),
        ], 'teamstream-react');

        $this->publishes([
            __DIR__.'/../routes/teamstream.php' => base_path('routes/teamstream.php'),
        ], 'teamstream-routes');

        $this->publishes([
            __DIR__.'/../stubs/Actions' => app_path('Actions/TeamStream'),
        ], 'teamstream-actions');
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
        if (file_exists(base_path('routes/teamstream.php'))) {
            Route::middleware(config('teamstream.middleware', ['web', 'auth']))
                ->group(base_path('routes/teamstream.php'));
            return;
        }

        Route::middleware(config('teamstream.middleware', ['web', 'auth']))
            ->group(__DIR__.'/../routes/teamstream.php');
    }
}
