<?php

declare(strict_types=1);

namespace Forumify\Core\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('forumify:platform:install')]
class InstallCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new CommandIO($input, $output);
        $io->title('Installing forumify platform');

        $application = $this->getApplication();
        if ($application === null) {
            $io->error('Unable to create sub-commands. Quitting...');
            return Command::FAILURE;
        }

        $result = $this->runSubcommand($application, $output, new ArrayInput([
            'command' => 'doctrine:database:create',
            '--if-not-exists' => true,
        ]));

        if ($result !== Command::SUCCESS) {
            return $this->fail($io, 'Unable to create database.');
        }

        $result = $this->runSubcommand($application, $output, new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
        ]));

        if ($result !== Command::SUCCESS) {
            return $this->fail($io, 'Unable to create database schema.');
        }

        $result = $this->runSubcommand($application, $output, new ArrayInput([
            'command' => 'forumify:platform:create-user',
            '--admin' => true,
        ]), 5);

        if ($result !== Command::SUCCESS) {
            $io->warning([
                'Unable to create admin user. There was probably some output above as to why.',
                'You will need an admin user to configure your forum.',
                'Try manually running "php bin/console forumify:platform:create-user --admin"'
            ]);
        }

        $io->success('forumify configured successfully!');
        return Command::SUCCESS;
    }

    private function runSubcommand(
        Application $application,
        OutputInterface $output,
        InputInterface $input,
        int $maxAttempts = 1
    ): int {
        $attempts = 0;

        do {
            $attempts++;
            if ($attempts > 1) {
                $output->writeln("Attempt $attempts/$maxAttempts");
            }

            $result = $application->doRun($input, $output);
        } while ($result !== Command::SUCCESS && $attempts < $maxAttempts);

        return $result;
    }

    private function fail(CommandIO $io, string $reason): int
    {
        $io->error($reason);
        return Command::FAILURE;
    }
}
