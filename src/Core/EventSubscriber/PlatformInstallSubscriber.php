<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Forumify\Core\Form\RegisterType;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\CreateUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Throwable;

#[AsEventListener('kernel.request')]
class PlatformInstallSubscriber extends AbstractController
{
    public const INSTALLED_SETTING = 'forumify.platform_installed';

    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly CreateUserService $createUserService,
        private readonly Security $security,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $installed = $this->settingRepository->get(self::INSTALLED_SETTING);
        if ($installed) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        if (str_starts_with($route, '_profiler') || str_starts_with($route, '_wdt')) {
            return;
        }

        $form = $this->createFormBuilder()
            ->add('forumName', TextType::class)
            ->add('adminUser', RegisterType::class)
            ->getForm();

        $form->handleRequest($event->getRequest());
        if (!$form->isSubmitted() || !$form->isValid()) {
            $response = $this->render('@Forumify/form/simple_form_page.html.twig', [
                'title' => 'install',
                'form' => $form->createView(),
            ]);
            $event->setResponse($response);
            return;
        }

        $data = $form->getData();
        try {
            $user = $this->createUserService->createAdmin($data['adminUser']);
            $this->security->login($user, 'security.authenticator.form_login.main');
        } catch (Throwable) {
            $this->addFlash('error', 'Unable to create initial user.');
            $event->setResponse($this->redirectToRoute('forumify_core_index'));
            return;
        }

        $this->settingRepository->setBulk([
            self::INSTALLED_SETTING => true,
            'forumify.title' => $data['forumName'],
        ]);

        $event->setResponse($this->redirectToRoute('forumify_core_index'));
    }
}
