<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RepositoryExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('repository', [RepositoryRuntime::class, 'getRepository']),
        ];
    }
}
