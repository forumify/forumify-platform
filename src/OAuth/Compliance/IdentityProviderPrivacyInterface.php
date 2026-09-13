<?php

declare(strict_types=1);

namespace Forumify\OAuth\Compliance;

interface IdentityProviderPrivacyInterface
{
    public function getPrivacyPolicyUrl(): ?string;

    public function transfersDataOutsideEea(): bool;
}
