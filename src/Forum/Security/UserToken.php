<?php

declare(strict_types=1);

namespace Forumify\Forum\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

class UserToken extends AbstractToken
{
    public function __construct(UserInterface $user)
    {
        parent::__construct($user->getRoles());
        $this->setUser($user);
    }
}
