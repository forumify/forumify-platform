<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Page\Entity\Page;
use Forumify\Page\Form\PageType;
use Forumify\Page\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/page/{slug?}', 'page')]
    public function __invoke(Request $request, PageRepository $pageRepository, ?Page $page): Response
    {
        $form = $this->createForm(PageType::class, $page);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page = $form->getData();
            $pageRepository->save($page);
            $this->addFlash('success', 'flashes.page_saved');
        }

        return $this->render('@Forumify/admin/page/page.html.twig', [
            'form' => $form->createView(),
            'page' => $page,
        ]);
    }
}
