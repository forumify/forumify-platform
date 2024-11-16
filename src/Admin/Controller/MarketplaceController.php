<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Exception;
use Forumify\Admin\Exception\MarketplaceNotConnectedException;
use Forumify\Admin\Service\MarketplaceConnectService;
use Forumify\Admin\Service\MarketplaceService;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Plugin\Service\PluginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('forumify.admin.settings.plugins.manage')]
#[Route('/marketplace', 'marketplace')]
class MarketplaceController extends AbstractController
{
    #[Route('', '')]
    public function __invoke(
        MarketplaceService $marketplace,
        PluginService $pluginService,
    ): Response {
        $error = null;
        try {
            $customer = $marketplace->getCustomer();
        } catch (MarketplaceNotConnectedException) {
            return $this->render('@Forumify/admin/marketplace/connect.html.twig');
        } catch (Exception $ex) {
            $customer = null;
            $error = $ex->getMessage();
        }

        if ($customer !== null) {
            $latestVersions = $pluginService->getLatestVersions();
            foreach ($customer['subscriptions'] as &$subscription) {
                $isInstalled = isset($latestVersions[$subscription['package']]);
                $subscription['installed'] = $isInstalled;
            }
        }

        return $this->render('@Forumify/admin/marketplace/marketplace.html.twig', [
            'customer' => $customer,
            'error' => $error,
        ]);
    }

    #[Route('/connect', '_connect')]
    public function connect(
        Request $request,
        MarketplaceConnectService $connectService
    ): Response {
        $session = $request->getSession();
        if ($session->has('oauth.state')) {
            $session->remove('oauth.state');
        }

        $state = bin2hex(random_bytes(5));
        $session->set('oauth.state', $state);
        return $this->redirect($connectService->getRedirectUrl($state));
    }

    #[Route('/connect/finish', '_connect_finish')]
    public function connectFinish(
        Request $request,
        MarketplaceConnectService $connectService,
    ): Response {
        $code = $request->get('code');
        if ($code === null) {
            return $this->render('@Forumify/admin/marketplace/connect.html.twig', [
                'error' => 'code_missing',
            ]);
        }

        $session = $request->getSession();
        $state = $request->get('state');
        if ($state !== $session->get('oauth.state')) {
            return $this->render('@Forumify/admin/marketplace/connect.html.twig', [
                'error' => 'state_mismatch',
            ]);
        }

        $connectService->registerClient($code, $request->getHttpHost());
        $this->addFlash('success', 'admin.marketplace.connect.success');
        return $this->redirectToRoute('forumify_admin_marketplace');
    }
}
