<?php
declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Forumify\Core\Entity\HierarchicalInterface;
use Forumify\Core\Entity\User;

interface SubscribableInterface extends HierarchicalInterface
{
    public function getCreatedBy(): User;
}
