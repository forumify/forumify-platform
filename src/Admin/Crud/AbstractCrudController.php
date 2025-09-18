<?php

declare(strict_types=1);

namespace Forumify\Admin\Crud;

use Doctrine\ORM\EntityManagerInterface;
use Forumify\Admin\Crud\Event\PostSaveCrudEvent;
use Forumify\Admin\Crud\Event\PreSaveCrudEvent;
use Forumify\Core\Entity\AccessControlledEntityInterface;
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

/**
 * @template TEntity of object
 */
abstract class AbstractCrudController extends AbstractController
{
    protected AbstractRepository $repository;
    protected TranslatorInterface $translator;
    protected EventDispatcherInterface $eventDispatcher;

    // overridable templates
    protected string $listTemplate = '@Forumify/admin/crud/list.html.twig';
    protected string $formTemplate = '@Forumify/admin/crud/form.html.twig';
    protected string $deleteTemplate = '@Forumify/admin/crud/delete.html.twig';

    // enable/disable routes
    protected bool $allowCreate = true;
    protected bool $allowEdit = true;
    protected bool $allowDelete = true;

    // permissions, by default access is allowed
    protected ?string $permissionView = null;
    protected ?string $permissionCreate = null;
    protected ?string $permissionEdit = null;
    protected ?string $permissionDelete = null;

    /**
     * @return class-string<TEntity> The classname for the entity this controller will act on, for example Forum::class
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
        if ($this->permissionView !== null) {
            $this->denyAccessUnlessGranted($this->permissionView);
        }

        return $this->render($this->listTemplate, $this->templateParams([
            'table' => $this->getTableName(),
        ]));
    }

    #[Route('/create', '_create')]
    public function create(Request $request): Response
    {
        if (!$this->can($this->allowCreate, $this->permissionCreate)) {
            $this->addCrudFlash('error', 'admin.crud.create_not_allowed');
            return $this->redirectToRoute($this->getRoute('list'));
        }

        return $this->handleForm($request);
    }

    #[Route('/{identifier}/edit', '_edit')]
    public function edit(Request $request, string $identifier): Response
    {
        if (!$this->can($this->allowEdit, $this->permissionEdit)) {
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
        if (!$this->can($this->allowDelete, $this->permissionDelete)) {
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

    /**
     * @param TEntity|null $data
     */
    protected function handleForm(Request $request, ?object $data = null): Response
    {
        $isNew = $data === null;
        $form = $this->getForm($data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $this->save($isNew, $form);
            $this->addCrudFlash('success', 'admin.crud.saved');

            if ($isNew && $entity instanceof AccessControlledEntityInterface) {
                return $this->redirectToRoute('forumify_admin_acl', (array)$entity->getACLParameters());
            }
            return $this->redirectAfterSave($entity, $isNew);
        }

        return $this->render($this->formTemplate, $this->templateParams([
            'form' => $form,
            'data' => $data,
        ]));
    }

    /**
     * @return TEntity
     */
    protected function save(bool $isNew, FormInterface $form): object
    {
        $entity = $form->getData();

        $this->eventDispatcher->dispatch(
            new PreSaveCrudEvent($isNew, $form, $entity),
            PreSaveCrudEvent::getName($this->getEntityClass()),
        );

        $this->repository->save($entity);

        $this->eventDispatcher->dispatch(
            new PostSaveCrudEvent($isNew, $form, $entity),
            PostSaveCrudEvent::getName($this->getEntityClass()),
        );

        return $entity;
    }

    /**
     * @param TEntity $entity
     */
    protected function redirectAfterSave(mixed $entity, bool $isNew): Response
    {
        return $this->redirectToRoute($this->getRoute('list'));
    }

    protected function templateParams(array $params = []): array
    {
        return [
            'translationPrefix' => $this->getTranslationPrefix(),
            'route' => $this->getRoute(),
            'capabilities' => [
                'create' => $this->can($this->allowCreate, $this->permissionCreate),
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

    private function can(bool $enabled, ?string $permission): bool
    {
        if (!$enabled) {
            return false;
        }

        return $permission === null || $this->isGranted($permission);
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
