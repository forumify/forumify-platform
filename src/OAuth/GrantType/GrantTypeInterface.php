<?php

declare(strict_types=1);

namespace Forumify\OAuth\GrantType;

use Forumify\OAuth\Entity\OAuthClient;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[AutoconfigureTag('forumify.oauth.grant_type')]
interface GrantTypeInterface
{
    public function getGrantType(): string;

    public function respondToRequest(Request $request, OAuthClient $client): JsonResponse;
}
