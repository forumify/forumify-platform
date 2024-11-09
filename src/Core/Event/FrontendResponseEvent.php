<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

use Symfony\Component\HttpFoundation\Response;

class FrontendResponseEvent extends FrontendEvent
{
    public function __construct(
        string $frontend,
        public Response $response
    ) {
        parent::__construct($frontend);
    }
}
