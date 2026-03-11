<?php

namespace LaravelStream\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    protected $signature = 'laravelstream:install
                            {stack=vue : The frontend stack (vue or react)}
                            {--teams : Enable team management features}
                            {--api : Enable API token features}
                            {--2fa : Enable two-factor authentication}
                            {--photos : Enable profile photo uploads}
                            {--no-deletion : Disable account deletion}
                            {--composer=global : Absolute path to the Composer binary}';

    protected $description = 'Install the LaravelStream package into a Laravel 12 starter kit application';

    public function handle(): int
    {
        $stack = $this->argument('stack');

        if (! in_array($stack, ['vue', 'react'])) {
            $this->error('Invalid stack. Supported stacks: vue, react');
            return self::FAILURE;
        }

        $this->info("Installing LaravelStream for the [{$stack}] stack...");

        // Publish config
        $this->callSilent('vendor:publish', ['--tag' => 'laravelstream-config', '--force' => true]);
        $this->info('✓ Config published.');

        // Publish migrations
        $this->callSilent('vendor:publish', ['--tag' => 'laravelstream-migrations', '--force' => true]);
        $this->info('✓ Migrations published.');

        // Publish frontend stubs for the selected stack
        $this->callSilent('vendor:publish', ['--tag' => "laravelstream-{$stack}", '--force' => true]);
        $this->info("✓ {$stack} frontend components published.");

        // Publish routes
        $this->callSilent('vendor:publish', ['--tag' => 'laravelstream-routes', '--force' => true]);
        $this->info('✓ Routes published.');

        // Publish action stubs
        $this->callSilent('vendor:publish', ['--tag' => 'laravelstream-actions', '--force' => true]);
        $this->info('✓ Action classes published to app/Actions/LaravelStream.');

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
        $this->components->info('LaravelStream installed successfully!');
        $this->showNextSteps($stack);

        return self::SUCCESS;
    }

    protected function publishServiceProvider(): void
    {
        $fs = new Filesystem();

        if (! $fs->exists(app_path('Providers/LaravelStreamServiceProvider.php'))) {
            $stub = $this->getServiceProviderStub();
            $fs->ensureDirectoryExists(app_path('Providers'));
            $fs->put(app_path('Providers/LaravelStreamServiceProvider.php'), $stub);
            $this->info('✓ LaravelStreamServiceProvider created.');
        }

        // Register it in bootstrap/providers.php (Laravel 11+)
        $providers = app_path('../bootstrap/providers.php');
        if (file_exists($providers)) {
            $content = file_get_contents($providers);
            if (! str_contains($content, 'LaravelStreamServiceProvider')) {
                $content = str_replace(
                    "return [",
                    "return [\n    App\\Providers\\LaravelStreamServiceProvider::class,",
                    $content
                );
                file_put_contents($providers, $content);
                $this->info('✓ LaravelStreamServiceProvider registered in bootstrap/providers.php.');
            }
        }
    }

    protected function getServiceProviderStub(): string
    {
        return <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelStream\LaravelStream;
use LaravelStream\Facades\LaravelStream as LaravelStreamFacade;
use LaravelStream\Contracts\CreatesTeams;
use LaravelStream\Contracts\UpdatesTeamNames;
use LaravelStream\Contracts\AddsTeamMembers;
use LaravelStream\Contracts\InvitesTeamMembers;
use LaravelStream\Contracts\RemovesTeamMembers;
use LaravelStream\Contracts\DeletesTeams;
use LaravelStream\Contracts\UpdatesUserProfileInformation;
use LaravelStream\Contracts\UpdatesUserPasswords;
use LaravelStream\Contracts\DeletesUsers;
use App\Actions\LaravelStream\CreateTeam;
use App\Actions\LaravelStream\UpdateTeamName;
use App\Actions\LaravelStream\AddTeamMember;
use App\Actions\LaravelStream\InviteTeamMember;
use App\Actions\LaravelStream\RemoveTeamMember;
use App\Actions\LaravelStream\DeleteTeam;
use App\Actions\LaravelStream\UpdateUserProfileInformation;
use App\Actions\LaravelStream\UpdateUserPassword;
use App\Actions\LaravelStream\DeleteUser;

class LaravelStreamServiceProvider extends ServiceProvider
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
        $this->app->make(LaravelStream::class)
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
            'LaravelStream\\Traits\\HasTeams',
            'LaravelStream\\Traits\\HasProfilePhoto',
            'LaravelStream\\Traits\\TwoFactorAuthenticatable',
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
            $this->info('✓ User model updated with LaravelStream traits.');
        }
    }

    protected function registerRoutes(): void
    {
        $webRoutes = base_path('routes/web.php');
        if (file_exists($webRoutes)) {
            $content = file_get_contents($webRoutes);
            if (! str_contains($content, 'laravelstream.php')) {
                file_put_contents($webRoutes, $content . "\n\nrequire __DIR__.'/laravelstream.php';\n");
                $this->info('✓ LaravelStream routes registered in routes/web.php.');
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
        $this->line('  1. Review <comment>config/laravelstream.php</comment> and enable/disable features.');
        $this->line('  2. Customise actions in <comment>app/Actions/LaravelStream/</comment>.');
        $this->line('  3. Register roles/permissions in <comment>app/Providers/LaravelStreamServiceProvider.php</comment>.');
        if ($stack === 'vue') {
            $this->line('  4. Run <comment>npm install && npm run dev</comment>.');
        } else {
            $this->line('  4. Run <comment>npm install && npm run dev</comment>.');
        }
        $this->line('  5. Add links to <comment>/profile</comment>, <comment>/teams</comment>, and <comment>/user/api-tokens</comment> in your nav.');
    }
}
