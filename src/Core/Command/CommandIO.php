<?php

declare(strict_types=1);

namespace Forumify\Core\Command;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class CommandIO extends SymfonyStyle
{
    public function error(array|string|ConstraintViolationListInterface $message): void
    {
        if ($message instanceof ConstraintViolationListInterface) {
            $messages = [];
            foreach ($message as $violation) {
                $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            $message = $messages;
        }

        parent::error($message);
    }
}
