<?php

declare(strict_types=1);

namespace Forumify\Api\Security\GrantType;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;

#[AutoconfigureTag('forumify.api.oauth.grant_type')]
interface GrantTypeInterface
{
    public function getType(): string;

    public function handle(Request $request);
}
