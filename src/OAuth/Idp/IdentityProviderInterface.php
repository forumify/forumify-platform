<?php

declare(strict_types=1);

namespace Forumify\OAuth\Idp;

use Forumify\OAuth\Entity\IdentityProvider;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

#[AutoconfigureTag('forumify.oauth.identity_provider')]
interface IdentityProviderInterface
{
    public static function getType(): string;

    /**
     * @return class-string<FormTypeInterface<*>>
     */
    public static function getDataType(): string;

    public function getButtonHtml(IdentityProvider $idp): string;

    /**
     * @throws IdentityProviderException
     */
    public function initLogin(IdentityProvider $idp): Response;

    /**
     * @throws IdentityProviderException
     */
    public function callback(IdentityProvider $idp, Request $request): ?UserInterface;
}
