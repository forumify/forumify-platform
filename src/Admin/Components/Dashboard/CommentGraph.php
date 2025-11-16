<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Repository\CommentRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @extends TotalGraph<Comment>
 */
#[AsTwigComponent('Forumify\\Admin\\CommentGraph', '@Forumify/admin/dashboard/components/tile.html.twig')]
class CommentGraph extends TotalGraph
{
    public function __construct(CommentRepository $commentRepository)
    {
        parent::__construct($commentRepository);
    }

    public function getTitle(): string
    {
        return 'admin.dashboard.comments';
    }

    public function getIcon(): string
    {
        return 'ph-chat-teardrop-text';
    }
}
