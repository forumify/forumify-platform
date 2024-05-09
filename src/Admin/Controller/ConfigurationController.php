<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\SettingsType;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\MediaService;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConfigurationController extends AbstractController
{
    #[Route('/configuration', 'configuration')]
    public function __invoke(
        Request $request,
        SettingRepository $settingRepository,
        MediaService $mediaService,
        FilesystemOperator $assetStorage,
        FilesystemOperator $avatarStorage,
    ): Response {
        $form = $this->createForm(SettingsType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $settings = [];

            $settings['forum.title'] = $data['title'];
            $settings['core.enable_registrations'] = (string)$data['enable_registrations'];
            $settings['core.enable_email_login'] = (string)$data['enable_email_login'];

            if ($data['logo'] !== null) {
                $settings['forum.logo'] = $mediaService->saveToFilesystem($assetStorage, $data['logo']);
            }

            if ($data['default_avatar'] !== null) {
                $settings['forum.default_avatar'] = $mediaService->saveToFilesystem($avatarStorage, $data['default_avatar']);
            }

            $settings['core.recaptcha.enabled'] = (string)$data['enable_recaptcha'];
            $settings['core.recaptcha.site_key'] = $data['recaptcha_site_key'];
            $settings['core.recaptcha.site_secret'] = $data['recaptcha_site_secret'];

            $settingRepository->setBulk($settings);

            $this->addFlash('success', 'flashes.settings_saved');
            return $this->redirectToRoute('forumify_admin_configuration');
        }

        return $this->render('@Forumify/admin/configuration/configuration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}