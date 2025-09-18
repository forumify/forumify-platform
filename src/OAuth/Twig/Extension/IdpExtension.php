<?php

declare(strict_types=1);

namespace Forumify\OAuth\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class IdpExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('idp_button', [IdpRuntime::class, 'getIdpButton'], ['is_safe' => ['html']]),
        ];
    }
}
