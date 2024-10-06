<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Forumify\Core\Form\RegisterType;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\CreateUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener('kernel.request')]
class PlatformInstallSubscriber extends AbstractController
{
    public const INSTALLED_SETTING = 'forumify.platform_installed';

    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly CreateUserService $createUserService,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $installed = $this->settingRepository->get(self::INSTALLED_SETTING);
        if ($installed) {
            return;
        }

        $request = $event->getRequest();
        if ($request->get('skipInstall')) {
            $this->settingRepository->set(self::INSTALLED_SETTING, true);
            $event->setResponse($this->redirectToRoute('forumify_core_index'));
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
                'cancelPath' => $this->generateUrl('forumify_core_index', ['skipInstall' => 1]),
            ]);
            $event->setResponse($response);
            return;
        }

        $data = $form->getData();
        $this->settingRepository->setBulk([
            self::INSTALLED_SETTING => true,
            'forumify.title' => $data['forumName'],
        ]);
        $this->createUserService->createAdmin($data['adminUser']);
        $event->setResponse($this->redirectToRoute('forumify_core_index'));
    }
}
