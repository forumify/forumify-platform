<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Forumify\Core\Form\RichTextEditorType;
use Symfony\Component\Form\FormInterface;

class RichTextWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'content.rich_text';
    }

    public function getCategory(): string
    {
        return 'content';
    }

    public function getPreview(): string
    {
        return '<article class="text-small p-2">
            Rich Text
        </article>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/rich_text.html.twig';
    }

    public function getSettingsForm(array $data = []): ?FormInterface
    {
        return $this->createForm($data)
            ->add('content', RichTextEditorType::class)
            ->getForm()
        ;
    }
}
