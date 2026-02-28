<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('forumify.spam_protection.service')]
interface SpamProtectionServiceInterface
{
    public function isEnabled(): bool;

    public function isBot(): bool;

    public function getJavascripts(string $formId): string;

    public function modifyButtonHtml(string $html): string;
}
