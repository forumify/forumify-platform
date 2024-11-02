<?php

declare(strict_types=1);

namespace Forumify\Core\Command;

use Forumify\Core\Repository\SettingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('forumify:platform:setting')]
class SettingCommand extends Command
{
    public function __construct(private readonly SettingRepository $settingRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('key', 'k', InputOption::VALUE_REQUIRED);
        $this->addOption('value', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new CommandIO($input, $output);
        $io->title('Configuring setting');

        $key = $input->getOption('key');
        $rawValue = $input->getOption('value');
        $value = json_decode($rawValue, true, 512, JSON_THROW_ON_ERROR);

        $io->writeln("Setting '$key' to: " . print_r($value, true));
        $this->settingRepository->set($key, $value);
        $io->success("Setting '$key' updated.");

        return Command::SUCCESS;
    }
}
