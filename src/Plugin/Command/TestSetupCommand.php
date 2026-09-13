<?php

declare(strict_types=1);

namespace Forumify\Plugin\Command;

use Composer\InstalledVersions;
use Forumify\Core\Command\CommandIO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

/**
 * Prepares a test database for a plugin's test suite, so plugins don't have to repeat
 * the same sequence of commands in their Makefile and CI workflow.
 */
#[AsCommand('forumify:plugins:test-setup', 'Recreate the test database and activate the plugin under test.')]
class TestSetupCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'plugin',
            InputArgument::OPTIONAL,
            'The plugin package to activate. Defaults to the package being tested.',
        );
        $this->addOption(
            'keep-database',
            null,
            InputOption::VALUE_NONE,
            'Migrate the existing database instead of dropping and recreating it.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new CommandIO($input, $output);
        $io->title('Preparing test database');

        // This drops the database, never let it run against a real installation.
        if ($this->environment !== 'test') {
            $io->error("This command only runs in the test environment, got \"$this->environment\".");
            return self::FAILURE;
        }

        /** @var string|null $plugin */
        $plugin = $input->getArgument('plugin') ?? $this->getRootPackage();
        if ($plugin === null) {
            $io->error('Unable to determine which plugin to activate, pass the package name as an argument.');
            return self::FAILURE;
        }

        $console = $this->getConsolePath();
        if ($console === null) {
            $io->error("Unable to locate a console binary at \"$this->projectDir/bin/console\".");
            return self::FAILURE;
        }

        $steps = [];
        if (!$input->getOption('keep-database')) {
            $steps[] = ['doctrine:database:drop', '--if-exists', '--force'];
            $steps[] = ['doctrine:database:create'];
        }

        $steps[] = ['doctrine:migrations:migrate', '--no-interaction'];
        $steps[] = ['forumify:platform:setting', '--key=forumify.platform_installed', '--value=true'];
        $steps[] = ['forumify:plugins:activate', $plugin];

        // Each step runs in its own process: migrations leave the shared connection in a
        // state the commands after it cannot reuse.
        foreach ($steps as $step) {
            $io->section(implode(' ', $step));

            $process = new Process([PHP_BINARY, $console, ...$step, '--env=test']);
            $process->setTimeout(null);
            $exitCode = $process->run(static fn (string $type, string $buffer) => $output->write($buffer));

            if ($exitCode !== self::SUCCESS) {
                $io->error("Command \"{$step[0]}\" failed, aborting.");
                return $exitCode;
            }
        }

        $io->success("Test database ready, $plugin is active.");

        return self::SUCCESS;
    }

    private function getConsolePath(): ?string
    {
        $console = $this->projectDir . '/bin/console';

        return is_file($console) ? $console : null;
    }

    private function getRootPackage(): ?string
    {
        /** @var array{name?: string} $rootPackage */
        $rootPackage = InstalledVersions::getRootPackage();

        return $rootPackage['name'] ?? null;
    }
}
