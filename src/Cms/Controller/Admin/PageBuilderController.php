<?php

declare(strict_types=1);

namespace Forumify\Cms\Controller\Admin;

use Forumify\Cms\Entity\Resource;
use Forumify\Cms\Widget\WidgetInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('pagebuilder', 'page_builder_')]
#[IsGranted('forumify.admin.cms.pages.manage')]
class PageBuilderController extends AbstractController
{
    /**
     * @param iterable<WidgetInterface> $widgets
     */
    public function __construct(
        #[AutowireIterator('forumify.cms.widget')]
        private readonly iterable $widgets,
        private readonly FilesystemOperator $resourceStorage,
    ) {
    }

    #[Route('/settings', 'settings', methods: ['POST'])]
    public function settings(Request $request): Response
    {
        $widgetName = $request->get('widget');
        $data = $request->toArray();

        $widget = $this->findWidget($widgetName);
        $form = $widget?->getSettingsForm($data);
        if ($form === null) {
            return new Response();
        }

        return $this->render('@Forumify/admin/cms/page/settings_form.html.twig', [
            'form' => $form,
            'widget' => $widget,
        ]);
    }

    private function findWidget(string $name): ?WidgetInterface
    {
        foreach ($this->widgets as $widget) {
            if ($widget->getName() === $name) {
                return $widget;
            }
        }
        return null;
    }

    #[Route('/resource/{slug}', 'resource')]
    public function previewResource(Resource $resource): Response
    {
        $path = $resource->getPath();
        $stream = $this->resourceStorage->readStream($path);
        return new StreamedResponse(function () use ($stream) {
            fpassthru($stream);
        }, headers: [
            'Content-Type' => $this->resourceStorage->mimeType($path),
            'Content-Length' => $this->resourceStorage->fileSize($path),
        ]);
    }
}
