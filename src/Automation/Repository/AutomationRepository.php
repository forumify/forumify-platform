<?php

declare(strict_types=1);

namespace Forumify\Automation\Repository;

use Forumify\Automation\Entity\Automation;
use Forumify\Core\Repository\AbstractRepository;

/**
 * @extends AbstractRepository<Automation>
 */
class AutomationRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Automation::class;
    }

    /**
     * @return array<Automation>
     */
    public function findByTriggerType(string $triggerType): array
    {
        return $this->findBy(['trigger' => $triggerType, 'enabled' => true]);
    }
}
