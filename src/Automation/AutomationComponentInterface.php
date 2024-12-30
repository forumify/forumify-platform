<?php

declare(strict_types=1);

namespace Forumify\Automation;

interface AutomationComponentInterface
{
    public static function getType(): string;
    public function getPayloadFormType(): ?string;
}
