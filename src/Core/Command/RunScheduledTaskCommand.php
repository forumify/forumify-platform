<?php

declare(strict_types=1);

namespace Forumify\Core\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Scheduler\Messenger\ServiceCallMessage;
use Symfony\Component\Scheduler\Messenger\ServiceCallMessageHandler;

#[AsCommand('forumify:scheduler:run', 'Run a scheduled task immediately.')]
class RunScheduledTaskCommand extends Command
{
    public function __construct(
        #[Autowire('@scheduler.messenger.service_call_message_handler')]
        private readonly ServiceCallMessageHandler $serviceCallMessageHandler
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('taskHandler', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Run scheduler task handler.');

        $handler = $input->getArgument('taskHandler');

        $handlersLocator = new HandlersLocator([
            ServiceCallMessage::class => [$this->serviceCallMessageHandler],
        ]);

        $messageBus = new MessageBus([
            new HandleMessageMiddleware($handlersLocator),
        ]);

        $io = new SymfonyStyle($input, $output);
        $io->info('Running schedule ' . $handler);

        $message = new ServiceCallMessage($handler);

        try {
            $messageBus->dispatch($message);
            $io->success('Task handled successfully');

            return Command::SUCCESS;
        } catch (ExceptionInterface $ex) {
            $io->error(\sprintf('Unable to run %s. Are you sure it exists?', $handler));

            return Command::FAILURE;
        }
    }
}
