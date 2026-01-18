<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Crud\AbstractCrudController;
use Forumify\Admin\Form\ForumTagType;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumTag;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractCrudController<ForumTag>
 */
#[Route('/forum-tags', 'forum_tags')]
class ForumTagController extends AbstractCrudController
{
    protected string $formTemplate = '@Forumify/admin/forum/tag/form.html.twig';

    protected ?string $permissionView = 'forumify.admin.forums.manage';
    protected ?string $permissionCreate = 'forumify.admin.forums.manage';
    protected ?string $permissionEdit = 'forumify.admin.forums.manage';
    protected ?string $permissionDelete = 'forumify.admin.forums.manage';

    private ?Forum $forum = null;

    public function __construct(
        private readonly ForumRepository $forumRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    protected function getEntityClass(): string
    {
        return ForumTag::class;
    }

    protected function getTableName(): string
    {
        return 'ForumTagTable';
    }

    protected function getForm(?object $data): FormInterface
    {
        if ($data === null) {
            $data = new ForumTag();
            $data->forum = $this->getForum();
        }

        return $this->createForm(ForumTagType::class, $data);
    }

    protected function templateParams(array $params = []): array
    {
        return parent::templateParams([
            ...$params,
            'forum' => $this->getForum(),
        ]);
    }

    private function getForum(): ?Forum
    {
        if ($this->forum !== null) {
            return $this->forum;
        }

        $forum = null;
        $forumSlug = $this->requestStack->getCurrentRequest()?->query->get('forum');
        if (!empty($forumSlug)) {
            $forum = $this->forumRepository->findOneBy(['slug' => $forumSlug]);
            if ($forum === null) {
                throw new NotFoundHttpException("Forum with slug $forumSlug does not exist.");
            }
        }

        return $this->forum = $forum;
    }

    protected function redirectAfterSave(mixed $entity, bool $isNew): Response
    {
        if ($entity->forum === null) {
            return parent::redirectAfterSave($entity, $isNew);
        }

        $response = $this->redirectToRoute('forumify_admin_forum', ['slug' => $entity->forum->getSlug()]);
        $response->setTargetUrl($response->getTargetUrl() . '#tab-tags');
        return $response;
    }
}
