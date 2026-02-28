<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\Service\SpamProtectionService;
use Twig\Extension\RuntimeExtensionInterface;

class SpamProtectionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly SpamProtectionService $spamProtectionService)
    {
    }

    public function getFormJavascripts(string $formId): string
    {
        return $this->spamProtectionService->getJavascripts($formId);
    }

    public function modifyButtonHtml(string $html): string
    {
        return $this->spamProtectionService->modifyButtonHtml($html);
    }
}
