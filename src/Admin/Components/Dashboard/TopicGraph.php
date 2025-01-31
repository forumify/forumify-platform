<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use Forumify\Forum\Repository\TopicRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Forumify\\Admin\\TopicGraph', '@Forumify/admin/dashboard/components/tile.html.twig')]
class TopicGraph extends TotalGraph
{
    public function __construct(TopicRepository $repository)
    {
        parent::__construct($repository);
    }

    public function getTitle(): string
    {
        return 'admin.dashboard.topics';
    }

    public function getIcon(): string
    {
        return 'ph-chats-teardrop';
    }
}
