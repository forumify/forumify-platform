<?php

declare(strict_types=1);

namespace Forumify\Cms\Twig\Extension;

use Forumify\Cms\Repository\ResourceRepository;
use Forumify\Cms\Repository\SnippetRepository;
use Forumify\Core\Service\HTMLSanitizer;
use Symfony\Component\Asset\Packages;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class CMSExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ResourceRepository $resourceRepository,
        private readonly SnippetRepository $snippetRepository,
        private readonly Environment $twig,
        private readonly Packages $packages,
        private readonly HTMLSanitizer $sanitizer,
    ) {
    }

    public function resource(string $slug): string
    {
        $resource = $this->resourceRepository->findOneBy(['slug' => $slug]);
        if ($resource === null) {
            return '';
        }

        return $this->packages->getUrl($resource->getPath(), 'forumify.resource');
    }

    public function snippet(string $slug): string
    {
        $snippet = $this->snippetRepository->findOneBy(['slug' => $slug]);
        if ($snippet === null) {
            return '';
        }

        if ($snippet->getType() === 'html') {
            return $this->twig
                ->createTemplate($snippet->getContent())
                ->render();
        }

        $sanitized = $this->sanitizer->sanitize($snippet->getContent());
        return "<div class='rich-text'>$sanitized</div>";
    }
}
