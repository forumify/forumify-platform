<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ComplianceExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('privacy_policy_path', [ComplianceRuntime::class, 'privacyPolicyPath']),
            new TwigFunction('privacy_disclosures', [ComplianceRuntime::class, 'privacyDisclosures']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sentence_list', [ComplianceRuntime::class, 'sentenceList']),
        ];
    }
}
