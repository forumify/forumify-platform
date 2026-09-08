<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use DateTime;
use Forumify\Admin\Form\ConfigurationType;
use Forumify\Core\Compliance\ComplianceService;
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

            $complianceBefore = $this->getComplianceSettings();
            $this->settingRepository->handleFormData($data);
            if ($this->getComplianceSettings() !== $complianceBefore) {
                $this->settingRepository->set(
                    ComplianceService::SETTING_LAST_UPDATED,
                    new DateTime()->format('Y-m-d'),
                );
            }

            $this->addFlash('success', 'flashes.settings_saved');
            return $this->redirectToRoute('forumify_admin_configuration');
        }

        return $this->render('@Forumify/admin/configuration/configuration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getComplianceSettings(): array
    {
        return array_filter(
            $this->settingRepository->getAll(),
            static fn (string $key) => str_starts_with($key, ComplianceService::SETTING_PREFIX)
                && $key !== ComplianceService::SETTING_LAST_UPDATED,
            ARRAY_FILTER_USE_KEY,
        );
    }
}
