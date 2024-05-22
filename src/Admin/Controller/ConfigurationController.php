<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\ConfigurationType;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\MediaService;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConfigurationController extends AbstractController
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly MediaService $mediaService,
        private readonly FilesystemOperator $assetStorage,
        private readonly FilesystemOperator $avatarStorage,
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

            $this->handleUnmappedFields($form);

            $this->addFlash('success', 'flashes.settings_saved');
            return $this->redirectToRoute('forumify_admin_configuration');
        }

        return $this->render('@Forumify/admin/configuration/configuration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function handleUnmappedFields(FormInterface $form): void
    {
        $settings = [];

        $newLogo = $form->get('newLogo')->getData();
        if ($newLogo !== null) {
            $settings['forumify.logo'] = $this->mediaService->saveToFilesystem($this->assetStorage, $newLogo);
        }

        $newDefaultAvatar = $form->get('newDefaultAvatar')->getData();
        if ($newDefaultAvatar !== null) {
            $settings['forumify.default_avatar'] = $this->mediaService->saveToFilesystem($this->avatarStorage, $newDefaultAvatar);
        }

        if (!empty($settings)) {
            $this->settingRepository->setBulk($settings);
        }
    }
}
