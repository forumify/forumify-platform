<?php

declare(strict_types=1);

namespace Forumify\Core\Command;

use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

#[AsCommand('forumify:database:fixtures')]
class LoadFixturesCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->runCommand('doctrine:database:drop', $output, ['-f', '--if-exists']);
        $this->runCommand('doctrine:database:create', $output);
        $this->runCommand('doctrine:migrations:migrate', $output, ['--no-interaction']);
        $this->runCommand('doctrine:fixtures:load', $output, ['--no-interaction']);
        $this->runCommand('forumify:forum:reindex-last-activity', $output);

        return Command::SUCCESS;
    }

    private function runCommand(string $command,OutputInterface $output, array $arguments = []): void
    {
        $php = (new PhpExecutableFinder())->find();

        $process = new Process([$php, 'bin/console', $command, ...$arguments]);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        $process->wait();

        if (!$process->isSuccessful()) {
            throw new RuntimeException("Unable to run $command. There is likely some output above explaining why.");
        }
    }
}
