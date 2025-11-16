<?php

declare(strict_types=1);

namespace Forumify\Cms\Controller\Admin;

use Forumify\Cms\Entity\Snippet;
use Forumify\Cms\Form\SnippetType;
use Forumify\Cms\Repository\SnippetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('snippets', 'snippet_')]
#[IsGranted('forumify.admin.cms.snippets.view')]
class SnippetController extends AbstractController
{
    public function __construct(
        private readonly SnippetRepository $snippetRepository,
    ) {
    }

    #[Route('', 'list')]
    public function list(): Response
    {
        return $this->render('@Forumify/admin/cms/snippet/list.html.twig');
    }

    #[Route('/create', 'create')]
    #[IsGranted('forumify.admin.cms.snippets.manage')]
    public function create(Request $request): Response
    {
        return $this->handleForm($request, null);
    }

    #[Route('/{slug:snippet}', 'edit')]
    #[IsGranted('forumify.admin.cms.snippets.manage')]
    public function edit(Request $request, Snippet $snippet): Response
    {
        return $this->handleForm($request, $snippet);
    }

    #[Route('/{slug:snippet}/delete', 'delete')]
    #[IsGranted('forumify.admin.cms.snippets.manage')]
    public function delete(Snippet $snippet, Request $request): Response
    {
        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/admin/cms/snippet/delete.html.twig', [
                'snippet' => $snippet,
            ]);
        }

        $this->snippetRepository->remove($snippet);

        $this->addFlash('success', 'flashes.snippet_removed');
        return $this->redirectToRoute('forumify_admin_cms_snippet_list');
    }

    private function handleForm(Request $request, ?Snippet $snippet): Response
    {
        /** @var FormInterface<Snippet> $form */
        $form = $this->createForm(SnippetType::class, $snippet);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Snippet $snippet */
            $snippet = $form->getData();
            $this->snippetRepository->save($snippet);

            $this->addFlash('success', 'flashes.snippet_saved');
            return $this->redirectToRoute('forumify_admin_cms_snippet_edit', [
                'slug' => $snippet->getSlug(),
            ]);
        }

        return $this->render('@Forumify/admin/cms/snippet/snippet.html.twig', [
            'form' => $form->createView(),
            'snippet' => $snippet,
        ]);
    }
}
