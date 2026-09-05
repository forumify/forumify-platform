<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\ConfigurationType;
use Forumify\Core\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('forumify.admin.configuration.manage')]
class ConfigurationController extends AbstractController
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
    ) {
    }

    #[Route('/configuration', 'configuration')]
    public function __invoke(Request $request): Response
    {
        $formData = $this->settingRepository->toFormData('forumify');
        $form = $this->createForm(ConfigurationType::class, $formData);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->settingRepository->handleFormData($data);

            $this->addFlash('success', 'flashes.settings_saved');
            return $this->redirectToRoute('forumify_admin_configuration');
        }

        return $this->render('@Forumify/admin/configuration/configuration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
