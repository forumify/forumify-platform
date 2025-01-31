<?php

declare(strict_types=1);

namespace Forumify\Automation\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Stringable;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class UserExpressionResolver
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @return array<User>
     */
    public function resolve(string $expr, ?array $payload): array
    {
        $payload ??= [];

        $expressionLanguage = new ExpressionLanguage();
        $evaluated = $expressionLanguage->evaluate($expr, $payload ?? []);
        $evaluated = is_array($evaluated) ? $evaluated : [$evaluated];

        return array_filter(array_map($this->findUser(...), $evaluated));
    }

    private function findUser(mixed $identifier): ?User
    {
        if ($identifier instanceof User) {
            return $identifier;
        }

        if (is_numeric($identifier)) {
            $user = $this->userRepository->find((int)$identifier);
            if ($user !== null) {
                return $user;
            }
        }

        if (is_scalar($identifier) || $identifier instanceof Stringable) {
            $user = $this->userRepository->findOneBy(['username' => (string)$identifier]);
            if ($user !== null) {
                return $user;
            }
        }

        return null;
    }
}
