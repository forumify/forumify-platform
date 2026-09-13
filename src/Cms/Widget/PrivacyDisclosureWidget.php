<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;

class PrivacyDisclosureWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'compliance.disclosures';
    }

    public function getCategory(): string
    {
        return 'compliance';
    }

    public function getPreview(): string
    {
        return '<div class="flex flex-col gap-1 p-2">
            <div style="height: 8px; width: 100%; background-color: var(--c-primary); border-radius: var(--border-radius)"></div>
            <div style="height: 8px; width: 80%; background-color: var(--border-color); border-radius: var(--border-radius)"></div>
            <div style="height: 8px; width: 90%; background-color: var(--border-color); border-radius: var(--border-radius)"></div>
            <div style="height: 8px; width: 70%; background-color: var(--border-color); border-radius: var(--border-radius)"></div>
        </div>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/privacy_disclosures.html.twig';
    }

    /**
     * @param array<string, mixed> $data
     * @return FormInterface<array<string, mixed>|null>
     */
    public function getSettingsForm(array $data = []): ?FormInterface
    {
        $data['showData'] = (bool)($data['showData'] ?? true);
        $data['showRecipients'] = (bool)($data['showRecipients'] ?? true);
        $data['showCookies'] = (bool)($data['showCookies'] ?? true);

        return $this->createForm($data)
            ->add('showData', CheckboxType::class, [
                'label' => 'admin.cms.widget.compliance.show_data',
                'required' => false,
            ])
            ->add('showRecipients', CheckboxType::class, [
                'label' => 'admin.cms.widget.compliance.show_recipients',
                'required' => false,
            ])
            ->add('showCookies', CheckboxType::class, [
                'label' => 'admin.cms.widget.compliance.show_cookies',
                'required' => false,
            ])
            ->getForm();
    }
}
