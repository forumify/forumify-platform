<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

abstract class AbstractWidget implements WidgetInterface
{
    private FormFactoryInterface $formFactory;
    private Environment $twig;

    public function getSettingsForm(array $data = []): ?FormInterface
    {
        return null;
    }

    protected function createForm(array $data = []): FormBuilderInterface
    {
        return $this->formFactory->createBuilder(data: $data, options: [
            'csrf_protection' => false,
        ]);
    }

    #[Required]
    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    protected function render(string $template, array $context = []): string
    {
        return $this->twig->render($template, $context);
    }

    #[Required]
    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }
}
