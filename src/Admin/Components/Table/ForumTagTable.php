<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Forumify\Core\Component\Table\AbstractTable;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumTag;
use Forumify\Forum\Repository\ForumTagRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Twig\Environment;

#[AsLiveComponent('ForumTagTable', '@Forumify/components/table/table.html.twig')]
#[IsGranted('forumify.admin.forums.manage')]
class ForumTagTable extends AbstractTable
{
    #[LiveProp]
    public ?Forum $forum = null;

    /**
     * @var array<ForumTag>
     */
    private ?array $tags = null;

    public function __construct(
        private readonly Environment $twig,
        private readonly ForumTagRepository $forumTagRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        $this->limit = 1_000_000;
    }

    protected function getData(): array
    {
        if ($this->tags !== null) {
            return $this->tags;
        }

        if ($this->forum === null) {
            $this->tags = $this->forumTagRepository->findAll();
        } else {
            $this->tags = $this->forumTagRepository->findByForum($this->forum);
        }

        uasort($this->tags, function (ForumTag $a, ForumTag $b): int {
            return strcmp($a->title, $b->title);
        });

        return $this->tags;
    }

    protected function getTotalCount(): int
    {
        return count($this->getData());
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn('title', [
                'field' => 'title',
                'label' => 'Tag',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderTag(...),
            ])
            ->addColumn('forum', [
                'field' => 'forum?.title',
                'searchable' => false,
                'sortable' => false,
                'renderer' => $this->renderForum(...),
            ])
            ->addColumn('allowInSubforums', [
                'field' => 'allowInSubforums',
                'searchable' => false,
                'sortable' => false,
                'label' => 'Subforums',
                'renderer' => fn(bool $allow, ForumTag $tag) => $tag->forum === null
                    ? ''
                    : ($allow ? 'Yes' : 'No'),
            ])
            ->addColumn('default', [
                'field' => 'default',
                'searchable' => false,
                'sortable' => false,
                'renderer' => fn(bool $default) => $default ? 'Yes' : 'No',
            ]);

        $this->addActionColumn($this->renderActionColumn(...));
    }

    private function renderTag(string $_, ForumTag $tag): string
    {
        return $this->twig->render('@Forumify/frontend/forum/forum_tag.html.twig', [
            'tag' => $tag,
        ]);
    }

    private function renderForum(?string $forumTitle, ForumTag $tag): string
    {
        $routeParams = [];
        if ($forum = $tag->forum) {
            $routeParams['slug'] = $forum->getSlug();
        }

        $forumTitle ??= 'All';
        if ($this->forum !== null) {
            if ($this->forum->getId() !== $tag->forum?->getId()) {
                $forumTitle .= ' (inherited)';
            } else {
                $forumTitle = "<strong>$forumTitle</strong>";
            }
        }

        $url = $tag->forum !== null
            ? $this->urlGenerator->generate('forumify_admin_forum', ['slug' => $tag->forum->getSlug()]) . '#tab-tags'
            : $this->urlGenerator->generate('forumify_admin_forum_tags_list');

        return "$forumTitle <a href='$url' target='blank'><i class='ph ph-arrow-square-out'></i></a>";
    }

    private function renderActionColumn(int $id, ForumTag $tag): string
    {
        $routeParams = ['identifier' => $id];
        if ($this->forum !== null) {
            $routeParams['forum'] = $this->forum->getSlug();
        }

        $actions = '';
        if ($this->forum === null || $this->forum->getId() === $tag->forum?->getId()) {
            $actions .= $this->renderAction('forumify_admin_forum_tags_edit', $routeParams, 'pencil-simple-line');
            $actions .= $this->renderAction('forumify_admin_forum_tags_delete', $routeParams, 'x');
        }

        return $actions;
    }
}
