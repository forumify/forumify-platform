<?php

declare(strict_types=1);

namespace Forumify\Plugin\Command;

use Forumify\Core\Command\CommandIO;
use Forumify\Plugin\Service\PluginService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('forumify:plugins:refresh')]
class RefreshPluginsCommand extends Command
{
    public function __construct(private readonly PluginService $pluginService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new CommandIO($input, $output);
        $io->title('Refreshing Plugins');

        $this->pluginService->refresh();

        $io->success('Plugins refreshed.');

        $this->getApplication()?->doRun(new ArrayInput([
            'command' => 'forumify:plugins:list'
        ]), $output);

        return Command::SUCCESS;
    }
}
