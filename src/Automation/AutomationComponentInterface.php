<?php

declare(strict_types=1);

namespace Forumify\Automation;

use Symfony\Component\Form\AbstractType;

interface AutomationComponentInterface
{
    public static function getType(): string;

    /**
     * @return class-string<AbstractType>
     */
    public function getPayloadFormType(): ?string;
}
