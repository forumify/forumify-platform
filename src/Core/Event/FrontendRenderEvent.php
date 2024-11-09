<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

use Forumify\Core\Repository\AbstractRepository;

class FrontendRenderEvent extends FrontendEvent
{
    public function __construct(
        string $frontend,
        public mixed $item,
        public AbstractRepository $repository,
        public array $templateParameters,
    ) {
        parent::__construct($frontend);
    }
}
