<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractWidget implements WidgetInterface
{
    private FormFactoryInterface $formFactory;

    public function getSettingsForm(array $data = []): ?FormInterface
    {
        return null;
    }

    protected function createForm(array $data = []): FormBuilderInterface
    {
        return $this->formFactory->createBuilder(data: $data, options: [
            'csrf_protection' => false
        ]);
    }

    #[Required]
    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }
}
