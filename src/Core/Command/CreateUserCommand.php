<?php

declare(strict_types=1);

namespace Forumify\Core\Command;

use Forumify\Core\Form\DTO\NewUser;
use Forumify\Core\Service\CreateUserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand('forumify:platform:create-user', 'Create a user using the command line.')]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly CreateUserService $createUserService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('admin', null, InputOption::VALUE_NONE, 'Create the user as admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $createAsAdmin = $input->getOption('admin');

        $io = new CommandIO($input, $output);
        $io->title('Create a forumify ' . ($createAsAdmin ? 'admin' : 'user'));

        $newUser = new NewUser();
        $newUser->setUsername($io->ask('Username'));
        $newUser->setEmail($io->ask('Email'));
        $newUser->setPassword($io->askHidden('Password (min. 8 characters)'));

        $errors = $this->validator->validate($newUser);
        if ($errors->count() > 0) {
            $io->error($errors);
            return Command::FAILURE;
        }

        $user = $createAsAdmin
            ? $this->createUserService->createAdmin($newUser)
            : $this->createUserService->createUser($newUser, false);

        $io->success("User {$user->getUsername()} created" . ($createAsAdmin ? ' with admin privileges.' : '.'));
        return Command::SUCCESS;
    }
}
