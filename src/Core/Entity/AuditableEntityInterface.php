<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

interface AuditableEntityInterface
{
    public function getIdentifierForAudit(): string;

    public function getNameForAudit(): string;
}
