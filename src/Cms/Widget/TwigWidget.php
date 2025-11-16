<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Forumify\Core\Form\CodeEditorType;
use Symfony\Component\Form\FormInterface;

class TwigWidget extends AbstractWidget
{

    public function getName(): string
    {
        return 'content.twig';
    }

    public function getCategory(): string
    {
        return 'content';
    }

    public function getPreview(): string
    {
        return '<div class="rich-text" data-setting-twig="innerHTML">
            <pre><code>{% for item in items %}
    {{ item.name }}
{% endfor %}</code></pre>
        </div>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/twig.html.twig';
    }

    /**
     * @param array<string, mixed> $data
     * @return FormInterface<array<string, mixed>|null>
     */
    public function getSettingsForm(array $data = []): ?FormInterface
    {
        return $this->createForm($data)
            ->add('twig', CodeEditorType::class, [
                'language' => 'twig',
            ])
            ->getForm()
        ;
    }
}
