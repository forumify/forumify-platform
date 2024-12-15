<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use Forumify\Forum\Repository\TopicRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Forumify\\TopicGraph', '@Forumify/admin/dashboard/graphs/topics.html.twig')]
class TopicGraph extends TotalGraph
{
    public function __construct(TopicRepository $repository)
    {
        parent::__construct($repository);
    }
}
