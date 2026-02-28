<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class SpamProtectionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('spam_protection_javascripts', [SpamProtectionRuntime::class, 'getFormJavascripts'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('spam_protection_submit', [SpamProtectionRuntime::class, 'modifyButtonHtml'], ['is_safe' => ['html']]),
        ];
    }
}
