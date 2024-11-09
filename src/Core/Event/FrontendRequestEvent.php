<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

use Symfony\Component\HttpFoundation\Request;

class FrontendRequestEvent extends FrontendEvent
{
    public function __construct(
        string $frontend,
        public Request $request,
    ) {
        parent::__construct($frontend);
    }
}
