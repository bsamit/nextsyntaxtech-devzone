<?php

namespace NextSyntaxTech\DevZone\Commands;

use Illuminate\Console\Command;
use NextSyntaxTech\DevZone\Services\DevZoneService;

class InstallDevZoneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devzone:install 
                            {--email= : Authorized Administrator Email} 
                            {--key= : Master Access Passkey/Token} 
                            {--path= : Dedicated Panel Access URL prefix} 
                            {--publish-views : Whether to publish Blade views for customization}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure NextSyntaxTech DevZone Developer Utility Panel';

    /**
     * Execute the console command.
     */
    public function handle(DevZoneService $service): int
    {
        $this->newLine();
        $this->line('  <bg=blue;fg=white;options=bold> NextSyntaxTech DevZone </> <fg=cyan;options=bold>Enterprise Installation Wizard</>');
        $this->line('  <fg=gray>Independent Developer Control & Maintenance Panel</>');
        $this->newLine();

        // 1. Publish Configuration
        $this->info('Publishing DevZone configuration file...');
        $this->call('vendor:publish', [
            '--tag' => 'devzone-config',
            '--force' => true,
        ]);

        // 2. Obtain Parameters (from options or interactive prompts)
        $email = $this->option('email');
        if (empty($email)) {
            $defaultEmail = config('devzone.admin_email', 'amitsaha.cse@gmail.com');
            $email = $this->ask('Enter Authorized Administrator Email', $defaultEmail);
        }

        while (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address format. Please provide a valid email.');
            $email = $this->ask('Enter Authorized Administrator Email');
        }

        $passkey = $this->option('key');
        if (empty($passkey)) {
            $passkey = $this->secret('Enter Master Developer Access Passkey / Token (min 6 characters)');
            while (empty($passkey) || strlen($passkey) < 6) {
                $this->error('Passkey must be at least 6 characters long.');
                $passkey = $this->secret('Enter Master Developer Access Passkey / Token (min 6 characters)');
            }
        }

        $path = $this->option('path');
        if (empty($path)) {
            $defaultPath = config('devzone.path', 'developer-panel');
            $path = $this->ask('Enter Dedicated Panel Access Path (URL prefix)', $defaultPath);
        }

        $path = trim(trim($path), '/');

        // 3. Save to .env
        $this->info('Writing credentials securely to your .env file...');
        $saved = $service->saveSetupCredentials($email, $passkey, $path);

        if (!$saved) {
            $this->error('Could not write to .env file. Please check file permissions or configure DEVZONE_* keys manually.');
        } else {
            $this->line('  <fg=green>✓</> .env configuration updated successfully.');
        }

        // 4. Optionally Publish Views
        $publishViews = $this->option('publish-views');
        if (!$publishViews && $this->input->isInteractive()) {
            $publishViews = $this->confirm('Would you like to publish DevZone Blade views for custom styling?', false);
        }

        if ($publishViews) {
            $this->call('vendor:publish', [
                '--tag' => 'devzone-views',
                '--force' => false,
            ]);
            $this->line('  <fg=green>✓</> Blade views published to <comment>resources/views/vendor/devzone</comment>.');
        }

        // 5. Clear Configuration Cache
        $this->callSilent('config:clear');

        $this->newLine();
        $this->line('  <bg=green;fg=white;options=bold> SUCCESS </> <fg=green;options=bold>DevZone is installed and ready!</>');
        $this->newLine();
        $this->line('  <fg=cyan>Access URL:</> <comment>' . url($path) . '</comment>');
        $this->line('  <fg=cyan>Admin Email:</> ' . $email);
        $this->line('  <fg=cyan>Passkey:</> <fg=yellow>Configured & Hidden</>');
        $this->newLine();
        $this->line('  <fg=gray>Tip: Access requires the Master Passkey. Session will automatically expire after idle timeout.</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
