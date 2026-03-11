<?php

namespace TeamStream\Commands;
namespace TeamStream\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    protected $signature = 'teamstream:install
                                protected $signature = 'teamstream:install
                            {stack=vue : The frontend stack (vue or react)}
                            {--teams : Enable team management features}
                            {--api : Enable API token features}
                            {--2fa : Enable two-factor authentication}
                            {--photos : Enable profile photo uploads}
                            {--no-deletion : Disable account deletion}
                            {--composer=global : Absolute path to the Composer binary}';

    protected $description = 'Install the TeamStream package into a Laravel 12 starter kit application';

    public function handle(): int
    {
        $stack = $this->argument('stack');

        if (! in_array($stack, ['vue', 'react'])) {
            $this->error('Invalid stack. Supported stacks: vue, react');
            return self::FAILURE;
        }

        $this->info("Installing TeamStream for the [{$stack}] stack...");
    $this->info("Installing TeamStream for the [{$stack}] stack...");

        // Publish config
        $this->callSilent('vendor:publish', ['--tag' => 'TeamStream-config', '--force' => true]);
            $this->callSilent('vendor:publish', ['--tag' => 'teamstream-config', '--force' => true]);
        $this->info('✓ Config published.');

        // Publish migrations
        $this->callSilent('vendor:publish', ['--tag' => 'TeamStream-migrations', '--force' => true]);
            $this->callSilent('vendor:publish', ['--tag' => 'teamstream-migrations', '--force' => true]);
        $this->info('✓ Migrations published.');

        // Publish frontend stubs for the selected stack
        $this->callSilent('vendor:publish', ['--tag' => "TeamStream-{$stack}", '--force' => true]);
            $this->callSilent('vendor:publish', ['--tag' => "teamstream-{$stack}", '--force' => true]);
        $this->info("✓ {$stack} frontend components published.");

        // Publish routes
        $this->callSilent('vendor:publish', ['--tag' => 'TeamStream-routes', '--force' => true]);
            $this->callSilent('vendor:publish', ['--tag' => 'teamstream-routes', '--force' => true]);
        $this->info('✓ Routes published.');

        // Publish action stubs
        $this->callSilent('vendor:publish', ['--tag' => 'TeamStream-actions', '--force' => true]);
            $this->callSilent('vendor:publish', ['--tag' => 'teamstream-actions', '--force' => true]);
            $this->info('713 Action classes published to app/Actions/TeamStream.');
        $this->info('✓ Action classes published to app/Actions/TeamStream.');

        // Publish and register the service provider
        $this->publishServiceProvider();

        // Update User model
        $this->updateUserModel($stack);

        // Register route in bootstrap/app.php or routes/web.php
        $this->registerRoutes();

        // Update config based on flags
        $this->updateConfig();

        $this->runMigrations();

        $this->newLine();
        $this->components->info('TeamStream installed successfully!');
        $this->showNextSteps($stack);

        return self::SUCCESS;
    }

    protected function publishServiceProvider(): void
    {
        $fs = new Filesystem();

        if (! $fs->exists(app_path('Providers/TeamStreamServiceProvider.php'))) {
            $stub = $this->getServiceProviderStub();
            $fs->ensureDirectoryExists(app_path('Providers'));
            $fs->put(app_path('Providers/TeamStreamServiceProvider.php'), $stub);
            $this->info('✓ TeamStreamServiceProvider created.');
        }

        // Register it in bootstrap/providers.php (Laravel 11+)
        $providers = app_path('../bootstrap/providers.php');
        if (file_exists($providers)) {
            $content = file_get_contents($providers);
            if (! str_contains($content, 'TeamStreamServiceProvider')) {
                $content = str_replace(
                    "return [",
                    "return [\n    App\\Providers\\TeamStreamServiceProvider::class,",
                    $content
                );
                file_put_contents($providers, $content);
                $this->info('✓ TeamStreamServiceProvider registered in bootstrap/providers.php.');
            }
        }
    }

    protected function getServiceProviderStub(): string
    {
        return <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use TeamStream\TeamStream;
use TeamStream\Facades\TeamStream as TeamStreamFacade;
use TeamStream\Contracts\CreatesTeams;
use TeamStream\Contracts\UpdatesTeamNames;
use TeamStream\Contracts\AddsTeamMembers;
use TeamStream\Contracts\InvitesTeamMembers;
use TeamStream\Contracts\RemovesTeamMembers;
use TeamStream\Contracts\DeletesTeams;
use TeamStream\Contracts\UpdatesUserProfileInformation;
use TeamStream\Contracts\UpdatesUserPasswords;
use TeamStream\Contracts\DeletesUsers;
use App\Actions\TeamStream\CreateTeam;
use App\Actions\TeamStream\UpdateTeamName;
use App\Actions\TeamStream\AddTeamMember;
use App\Actions\TeamStream\InviteTeamMember;
use App\Actions\TeamStream\RemoveTeamMember;
use App\Actions\TeamStream\DeleteTeam;
use App\Actions\TeamStream\UpdateUserProfileInformation;
use App\Actions\TeamStream\UpdateUserPassword;
use App\Actions\TeamStream\DeleteUser;

class TeamStreamServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CreatesTeams::class, CreateTeam::class);
        $this->app->singleton(UpdatesTeamNames::class, UpdateTeamName::class);
        $this->app->singleton(AddsTeamMembers::class, AddTeamMember::class);
        $this->app->singleton(InvitesTeamMembers::class, InviteTeamMember::class);
        $this->app->singleton(RemovesTeamMembers::class, RemoveTeamMember::class);
        $this->app->singleton(DeletesTeams::class, DeleteTeam::class);
        $this->app->singleton(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
        $this->app->singleton(UpdatesUserPasswords::class, UpdateUserPassword::class);
        $this->app->singleton(DeletesUsers::class, DeleteUser::class);
    }

    public function boot(): void
    {
        $this->app->make(TeamStream::class)
            ->permissions(['read', 'create', 'update', 'delete'])
            ->defaultApiTokenPermissions(['read'])
            ->role('admin', 'Administrator', ['*'], 'Full access to all team resources')
            ->role('editor', 'Editor', ['read', 'create', 'update'], 'Can view and edit resources')
            ->role('viewer', 'Viewer', ['read'], 'Can only view resources');
    }
}
PHP;
    }

    protected function updateUserModel(string $stack): void
    {
        $userModel = app_path('Models/User.php');
        if (! file_exists($userModel)) {
            $this->warn('Could not find app/Models/User.php – please add traits manually.');
            return;
        }

        $content = file_get_contents($userModel);

        $traits = [
            'TeamStream\\Traits\\HasTeams',
            'TeamStream\\Traits\\HasProfilePhoto',
            'TeamStream\\Traits\\TwoFactorAuthenticatable',
            'Laravel\\Sanctum\\HasApiTokens',
        ];

        $useStatements = '';
        $traitUses = '';

        foreach ($traits as $trait) {
            $shortName = class_basename($trait);
            if (! str_contains($content, $shortName)) {
                $useStatements .= "use {$trait};\n";
                $traitUses .= "    use {$shortName};\n";
            }
        }

        if ($useStatements) {
            $content = str_replace("use Illuminate\\Foundation\\Auth\\User as Authenticatable;",
                "use Illuminate\\Foundation\\Auth\\User as Authenticatable;\n{$useStatements}",
                $content);

            $content = preg_replace('/use HasFactory, Notifiable;/',
                "use HasFactory, Notifiable;\n{$traitUses}",
                $content, 1);

            file_put_contents($userModel, $content);
            $this->info('✓ User model updated with TeamStream traits.');
        }
    }

    protected function registerRoutes(): void
    {
        $webRoutes = base_path('routes/web.php');
        if (file_exists($webRoutes)) {
            $content = file_get_contents($webRoutes);
            if (! str_contains($content, 'TeamStream.php')) {
                file_put_contents($webRoutes, $content . "\n\nrequire __DIR__.'/TeamStream.php';\n");
                $this->info('✓ TeamStream routes registered in routes/web.php.');
            }
        }
    }

    protected function updateConfig(): void
    {
        // Features are set in config by default; installer flags can override
        // This is a placeholder for future env-based configuration tweaks
    }

    protected function runMigrations(): void
    {
        if ($this->confirm('Would you like to run the migrations now?', true)) {
            $this->call('migrate');
        }
    }

    protected function showNextSteps(string $stack): void
    {
        $this->components->info('Next steps:');
        $this->line('  1. Review <comment>config/TeamStream.php</comment> and enable/disable features.');
        $this->line('  2. Customise actions in <comment>app/Actions/TeamStream/</comment>.');
        $this->line('  3. Register roles/permissions in <comment>app/Providers/TeamStreamServiceProvider.php</comment>.');
        if ($stack === 'vue') {
            $this->line('  4. Run <comment>npm install && npm run dev</comment>.');
        } else {
            $this->line('  4. Run <comment>npm install && npm run dev</comment>.');
        }
        $this->line('  5. Add links to <comment>/profile</comment>, <comment>/teams</comment>, and <comment>/user/api-tokens</comment> in your nav.');
    }
}
