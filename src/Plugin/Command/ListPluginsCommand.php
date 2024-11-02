<?php

declare(strict_types=1);

namespace Forumify\Plugin\Command;

use Forumify\Core\Command\CommandIO;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('forumify:plugins:list')]
class ListPluginsCommand extends Command
{
    public function __construct(private readonly PluginRepository $pluginRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new CommandIO($input, $output);
        $io->title('Listing Plugins');

        $plugins = $this->pluginRepository->findAll();

        $io->table(
            ['id', 'name', 'author', 'active'],
            array_map($this->transformPlugin(...), $plugins),
        );
        $io->writeln('Missing any? Run forumify:plugins:refresh to reload plugins from composer.');

        return Command::SUCCESS;
    }

    private function transformPlugin(Plugin $plugin): array
    {
        $metadata = $plugin->getPlugin()->getPluginMetadata();
        return [
            $plugin->getId(),
            $metadata->name,
            $metadata->author,
            $plugin->isActive(),
        ];
    }
}
