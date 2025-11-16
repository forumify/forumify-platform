<?php

declare(strict_types=1);

namespace Forumify\OAuth\Controller;

use Forumify\OAuth\Entity\IdentityProvider;
use Forumify\OAuth\Entity\IdentityProviderUser;
use Forumify\OAuth\Idp\IdentityProviderInterface;
use Forumify\OAuth\Repository\IdentityProviderUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
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
        private readonly IdentityProviderUserRepository $idpUserRepository,
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

    #[Route('/{id}/unlink', 'unlink')]
    public function unlink(IdentityProviderUser $idpUser, Request $request): Response
    {
        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/frontend/auth/unlink_idp.html.twig', [
                'idpUser' => $idpUser,
            ]);
        }

        $this->idpUserRepository->remove($idpUser);

        $this->addFlash('success', 'account_settings.account_unlinked');
        return $this->redirectToRoute('forumify_core_settings');
    }
}
