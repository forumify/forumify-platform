<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\Compliance\ComplianceService;
use Forumify\Core\Compliance\PrivacyDisclosureRegistry;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class ComplianceRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ComplianceService $complianceService,
        private readonly PrivacyDisclosureRegistry $registry,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function privacyPolicyPath(): ?string
    {
        if (!$this->complianceService->isEnabled()) {
            return null;
        }

        return $this->urlGenerator->generate('forumify_core_privacy_policy');
    }

    public function privacyDisclosures(): PrivacyDisclosureRegistry
    {
        return $this->registry;
    }

    /**
     * @param array<string> $items
     */
    public function sentenceList(array $items): string
    {
        $items = array_values(array_filter($items));
        $last = array_pop($items);
        if ($last === null) {
            return '';
        }

        if (empty($items)) {
            return $last;
        }

        $conjunction = $this->translator->trans('privacy_policy.list_and');
        return implode(', ', $items) . " $conjunction " . $last;
    }
}
