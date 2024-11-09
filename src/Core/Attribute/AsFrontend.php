<?php

declare(strict_types=1);

namespace Forumify\Core\Attribute;

use Symfony\Component\Routing\Route;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsFrontend
{
    public function __construct(
        /**
         * @var string Name of the frontend, can also be used in twig's "{{ path('name') }}"
         */
        public readonly string $name,

        /**
         * @var Route The route this frontend will be attached to
         */
        public readonly Route $route,

        /**
         * @var string Template to render
         *      If an entity is found, the parameters "item" and "repository" will be passed to the template
         */
        public readonly ?string $template = null,

        /**
         * @var string|null The identifier to use when searching for the entity, for example "slug" or "id"
         */
        public readonly ?string $identifier = null,

        /**
         * @var string|null The permission to check for.
         *      If set, the entity must implement AccessControlledEntityInterface and this permission must be in getACLPermissions.
         */
        public readonly ?string $permission = null,
    ) {
    }
}
