<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;

class ButtonWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'content.button';
    }

    public function getCategory(): string
    {
        return 'content';
    }

    public function getPreview(): string
    {
        return '<div>
            <a
                class="btn-primary"
                href="#"
                data-setting-title="innerText"
                data-setting-type="className"
            >Button Title</a>
        </div>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/button.html.twig';
    }

    /**
     * @param array<string, mixed> $data
     * @return FormInterface<array<string, mixed>|null>
     */
    public function getSettingsForm(array $data = []): ?FormInterface
    {
        $data['openInNewTab'] = (bool)($data['openInNewTab'] ?? false);
        return $this->createForm($data)
            ->add('title', TextType::class)
            ->add('href', TextType::class, [
                'help' => 'admin.cms.widget.button.href_help',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Primary' => 'btn-primary',
                    'Outlined' => 'btn-outlined',
                    'Call To Action' => 'btn-cta',
                    'Link' => 'btn-link',
                ],
            ])
            ->add('openInNewTab', CheckboxType::class, [
                'required' => false,
            ])
            ->getForm()
        ;
    }
}
