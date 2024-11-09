<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

use Forumify\Core\Controller\FrontendController;

/**
 * Events to hook into the AsFrontend request handling cycle.
 *
 * @see FrontendController
 */
class FrontendEvents
{
    /**
     * Thrown when an AsFrontend request is matched and passed to the FrontendController
     * This event can be used to add additional request parameters.
     */
    public const REQUEST = 'forumify.frontend.request';

    /**
     * Thrown before rendering the template.
     * This event can be used to modify the parameters passed to the template.
     */
    public const RENDER = 'forumify.frontend.render';

    /**
     * Thrown before the response is sent to the client.
     * This event can be used to add or modify the response body and/or headers.
     */
    public const RESPONSE = 'forumify.frontend.response';

    public static function getName(string $event, string $frontend): string
    {
        return $event . '.' . $frontend;
    }
}
