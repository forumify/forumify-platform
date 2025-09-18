<?php

declare(strict_types=1);

namespace Forumify\OAuth\Controller;

use Forumify\OAuth\Entity\IdentityProvider;
use Forumify\OAuth\Idp\IdentityProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('', 'idp_')]
class IdentityProviderController extends AbstractController
{
    /**
     * @param iterable<IdentityProviderInterface> $idpTypes
     */
    public function __construct(
        #[AutowireIterator('forumify.oauth.identity_provider', defaultIndexMethod: 'getType')]
        private readonly iterable $idpTypes,
    ) {
    }

    #[Route('/{slug:idp}/init', 'init')]
    public function init(IdentityProvider $idp): Response
    {
        /** @var array<string, IdentityProviderInterface> $idpTypes */
        $idpTypes = iterator_to_array($this->idpTypes);
        $idpType = $idpTypes[$idp->getType()] ?? null;
        if ($idpType === null) {
            $this->addFlash('error', 'login.idp.unknown_type');
            return $this->redirectToRoute('forumify_core_login');
        }

        return $idpType->initLogin($idp);
    }

    #[Route('/{slug:idp}/callback', 'callback')]
    public function callback(): Response
    {
        /** @see Forumify\OAuth\Security\IdentityProviderAuthenticator */
        return $this->redirectToRoute('forumify_core_index');
    }
}
