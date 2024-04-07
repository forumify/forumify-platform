<?php

declare(strict_types=1);

namespace Forumify\Cms\MenuBuilder\MenuType;

use Forumify\Cms\MenuBuilder\Form\PagePayloadType;
use Forumify\Cms\Repository\PageRepository;
use Forumify\Core\Entity\MenuItem;
use Forumify\Core\MenuBuilder\MenuType\UrlMenuType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PageMenuType extends UrlMenuType
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getType(): string
    {
        return 'page';
    }

    public function getPayloadFormType(): ?string
    {
        return PagePayloadType::class;
    }

    protected function getUrl(MenuItem $item): string
    {
        $pageId = $item->getPayloadValue('page');
        if (empty($pageId)) {
            return '';
        }

        $page = $this->pageRepository->find($pageId);
        if ($page === null) {
            return '';
        }

        return $this->urlGenerator->generate('forumify_cms_page', [
            'urlKey' => $page->getUrlKey(),
        ]);
    }
}
