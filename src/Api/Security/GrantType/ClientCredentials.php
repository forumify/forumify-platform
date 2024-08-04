<?php

declare(strict_types=1);

namespace Forumify\Api\Security\GrantType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RFC6749 4.4 Client Credentials
 */
class ClientCredentials implements GrantTypeInterface
{
    public function getType(): string
    {
        return 'client_credentials';
    }

    public function handle(Request $request): Response
    {
    }
}
