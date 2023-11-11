<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Forumify\Page\Entity\Page;
use Forumify\Page\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;

#[AsLiveComponent(template: '@Forumify/admin/components/page_browser.html.twig', name: 'PageBrowser')]
class PageBrowser extends AbstractController
{
    #[LiveProp(writable: true)]
    public string $search = '';

    #[LiveProp]
    public ?Page $page = null;

    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly Security $security,
    ) {
    }

    public function __invoke(): void
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException();
        }
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function getPageSlugs(): array
    {
        return $this->pageRepository->createQueryBuilder('p')
            ->select('p.slug')
            ->where('p.slug LIKE :search')
            ->setParameter('search', "%{$this->search}%")
            ->setMaxResults(50)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
