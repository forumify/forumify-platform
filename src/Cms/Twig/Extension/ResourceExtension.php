<?php

declare(strict_types=1);

namespace Forumify\Cms\Twig\Extension;

use Forumify\Cms\Repository\ResourceRepository;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ResourceExtension extends AbstractExtension
{
    public function __construct(
        private readonly ResourceRepository $resourceRepository,
        private readonly Packages $packages,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('resource', $this->resource(...)),
        ];
    }

    public function resource(string $slug): string
    {
        $resource = $this->resourceRepository->findOneBy(['slug' => $slug]);
        if ($resource === null) {
            return '';
        }

        return $this->packages->getUrl($resource->getPath(), 'forumify.resource');
    }
}
