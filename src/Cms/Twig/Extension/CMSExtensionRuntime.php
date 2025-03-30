<?php

declare(strict_types=1);

namespace Forumify\Cms\Twig\Extension;

use Forumify\Cms\Repository\ResourceRepository;
use Forumify\Cms\Repository\SnippetRepository;
use Forumify\Cms\Widget\WidgetInterface;
use Forumify\Core\Service\HTMLSanitizer;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class CMSExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var array<string, WidgetInterface>|null
     */
    private ?array $widgetMemo = null;

    /**
     * @param iterable<WidgetInterface> $widgets
     */
    public function __construct(
        private readonly ResourceRepository $resourceRepository,
        private readonly SnippetRepository $snippetRepository,
        private readonly Environment $twig,
        private readonly Packages $packages,
        private readonly HTMLSanitizer $sanitizer,
        #[AutowireIterator('forumify.cms.widget')]
        private readonly iterable $widgets,
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

    public function widget(string $widget, array $settings = [], array $slots = []): string
    {
        $widget = $this->findWidget($widget);
        if ($widget === null) {
            return '';
        }

        return $this->twig->render($widget->getTemplate(), [
            'widget' => [
                'slots' => $slots,
                'settings' => $settings,
            ],
        ]);
    }

    public function widgetTemplate(array $widget): string
    {
        $widget = $this->findWidget($widget['widget']);
        return $widget->getTemplate();
    }

    private function findWidget(string $name): ?WidgetInterface
    {
        if ($this->widgetMemo !== null) {
            return $this->widgetMemo[$name];
        }

        $this->widgetMemo = [];
        foreach ($this->widgets as $widget) {
            $this->widgetMemo[$widget->getName()] = $widget;
        }
        return $this->findWidget($name);
    }
}
