<?php

declare(strict_types=1);

namespace Forumify\Admin\Crud;

use Doctrine\ORM\EntityManagerInterface;
use Forumify\Admin\Crud\Event\PostSaveCrudEvent;
use Forumify\Admin\Crud\Event\PreSaveCrudEvent;
use Forumify\Core\Repository\AbstractRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;

use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\String\u;

abstract class AbstractCrudController extends AbstractController
{
    protected AbstractRepository $repository;
    protected TranslatorInterface $translator;
    protected EventDispatcherInterface $eventDispatcher;

    // overridable templates
    protected string $listTemplate = '@Forumify/admin/crud/list.html.twig';
    protected string $formTemplate = '@Forumify/admin/crud/form.html.twig';
    protected string $deleteTemplate = '@Forumify/admin/crud/delete.html.twig';

    protected bool $allowAdd = true;
    protected bool $allowEdit = true;
    protected bool $allowDelete = true;

    /**
     * @return string The classname for the entity this controller will act on, for example Forum::class
     */
    abstract protected function getEntityClass(): string;

    /**
     * @return string Name of the LiveComponent for the table that is shown on the list page
     */
    abstract protected function getTableName(): string;

    /**
     * @return FormInterface Gets the form to use.
     *
     * You can use `$this->createForm(MyFormType::class, $data);` to re-use an existing form,
     * or `$this->createFormBuilder();` to build a one-off form.
     */
    abstract protected function getForm(?object $data): FormInterface;

    #[Route('', '_list')]
    public function list(): Response
    {
        return $this->render($this->listTemplate, $this->templateParams([
            'table' => $this->getTableName(),
        ]));
    }

    #[Route('/create', '_create')]
    public function create(Request $request): Response
    {
        if (!$this->allowAdd) {
            $this->addCrudFlash('error', 'admin.crud.create_not_allowed');
            return $this->redirectToRoute($this->getRoute('list'));
        }

        return $this->handleForm($request);
    }

    #[Route('/{identifier}/edit', '_edit')]
    public function edit(Request $request, string $identifier): Response
    {
        if (!$this->allowEdit) {
            $this->addCrudFlash('error', 'admin.crud.edit_not_allowed');
            return $this->redirectToRoute($this->getRoute('list'));
        }

        $data = $this->repository->find($identifier);
        if ($data === null) {
            throw $this->createNotFoundException("Entity {$this->getEntityClass()} with $identifier could not be found.");
        }

        return $this->handleForm($request, $data);
    }

    #[Route('/{identifier}/delete', '_delete')]
    public function delete(Request $request, string $identifier): Response
    {
        if (!$this->allowDelete) {
            $this->addCrudFlash('error', 'admin.crud.delete_not_allowed');
            return $this->redirectToRoute($this->getRoute('list'));
        }

        if (!$request->get('confirmed')) {
            return $this->render($this->deleteTemplate, $this->templateParams());
        }

        $data = $this->repository->find($identifier);
        if ($data !== null) {
            $this->repository->remove($data);
        }

        $this->addCrudFlash('success', 'admin.crud.deleted');
        return $this->redirectToRoute($this->getRoute('list'));
    }

    private function handleForm(Request $request, ?object $data = null): Response
    {
        $form = $this->getForm($data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();

            $this->eventDispatcher->dispatch(
                new PreSaveCrudEvent($data === null, $form, $entity),
                PreSaveCrudEvent::getName($this->getEntityClass()),
            );

            $this->repository->save($entity);

            $this->eventDispatcher->dispatch(
                new PostSaveCrudEvent($data === null, $form, $entity),
                PostSaveCrudEvent::getName($this->getEntityClass()),
            );

            $this->addCrudFlash('success', 'admin.crud.saved');
            return $this->redirectToRoute($this->getRoute('list'));
        }

        return $this->render($this->formTemplate, $this->templateParams([
            'form' => $form,
            'data' => $data,
        ]));
    }

    private function templateParams(array $params = []): array
    {
        return [
            'translationPrefix' => $this->getTranslationPrefix(),
            'route' => $this->getRoute(),
            'capabilities' => [
                'create' => $this->allowAdd,
                'edit' => $this->allowEdit,
                'delete' => $this->allowDelete,
            ],
            ...$params,
        ];
    }

    protected function getRoute(?string $suffix = null): string
    {
        $requestRoute = $this->container->get('request_stack')->getCurrentRequest()?->get('_route') ?? '';

        $route = u($requestRoute)->beforeLast('_');
        if ($suffix !== null) {
            $route = $route->append('_' . $suffix);
        }

        return $route->toString();
    }

    /**
     * @return string a prefix used for translations, default: 'admin.entity_class.crud.'
     */
    protected function getTranslationPrefix(): string
    {
        return u($this->getEntityClass())
            ->afterLast('\\')
            ->snake()
            ->prepend('admin.')
            ->append('.crud.')
            ->toString();
    }

    private function addCrudFlash(string $type, string $key): void
    {
        $prefix = $this->getTranslationPrefix();
        $message = $this->translator->trans($key, [
            'single' => $this->translator->trans($prefix . 'single'),
            'plural' => $this->translator->trans($prefix . 'plural'),
        ]);
        $this->addFlash($type, $message);
    }

    #[Required]
    public function setServices(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $repository = $em->getRepository($this->getEntityClass());
        if (!$repository instanceof AbstractRepository) {
            throw new \RuntimeException('Your entity must have a repository that extends ' . AbstractRepository::class);
        }

        $this->repository = $repository;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }
}
