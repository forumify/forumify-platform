<?php

declare(strict_types=1);

namespace Forumify\Plugin\Command;

use Forumify\Core\Command\CommandIO;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('forumify:plugins:activate')]
class ActivatePluginCommand extends Command
{
    public function __construct(private readonly PluginRepository $pluginRepository)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('plugin', InputArgument::REQUIRED, 'The plugin package name. For example "forumify/cool-plugin".');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getApplication()?->doRun(new ArrayInput([
            'command' => 'forumify:plugins:refresh',
        ]), $output);

        $io = new CommandIO($input, $output);
        $io->title('Activating Plugin');

        $package = $input->getArgument('plugin');
        /** @var Plugin|null $plugin */
        $plugin = $this->pluginRepository->findOneBy(['package' => $package]);
        if ($plugin === null) {
            $io->error("$package is not installed.");
            return self::FAILURE;
        }

        if ($plugin->isActive()) {
            $io->warning("$package is already active.");
            return self::SUCCESS;
        }

        $plugin->setActive(true);
        $this->pluginRepository->save($plugin);

        $io->success("$package activated.");

        $this->getApplication()?->doRun(new ArrayInput([
            'command' => 'forumify:plugins:list',
        ]), $output);

        return self::SUCCESS;
    }
}
