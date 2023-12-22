<?php

declare(strict_types=1);

namespace Forumify\Core\Command;

use Forumify\Core\Form\NewUser;
use Forumify\Core\Repository\RoleRepository;
use Forumify\Core\Repository\UserRepository;
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
        private readonly RoleRepository $roleRepository,
        private readonly UserRepository $userRepository,
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

        $user = $this->createUserService->createUser($newUser, false);
        if ($createAsAdmin) {
            $adminRole = $this->roleRepository->findOneBy(['slug' => 'super-admin']);
            $user->setRoleEntities([$adminRole]);
        }

        $this->userRepository->save($user);

        $io->success("User {$user->getUsername()} created" . ($createAsAdmin ? ' with admin privileges.' : '.'));
        return Command::SUCCESS;
    }
}
