<?php

declare(strict_types=1);

namespace Forumify\Forum\Command;

use Forumify\Forum\Service\ReindexLastActivityService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'forumify:forum:reindex-last-activity',
    description: 'Manually reindex last activity on forums in case it goes out of sync'
)]
class ReindexLastActivityCommand extends Command
{
    public function __construct(
        private readonly ReindexLastActivityService $reindexService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->reindexService->reindexAll();

        return Command::SUCCESS;
    }
}
