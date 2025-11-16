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
        return '<article class="rich-text" data-setting-content="innerHTML">
            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Et quod fugit non ut, modi hic vero blanditiis commodi dignissimos ad suscipit aperiam enim dolorem ratione recusandae incidunt eos laboriosam minima.
        </article>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/rich_text.html.twig';
    }

    /**
     * @param array<string, mixed> $data
     * @return FormInterface<array<string, mixed>|null>
     */
    public function getSettingsForm(array $data = []): ?FormInterface
    {
        return $this->createForm($data)
            ->add('content', RichTextEditorType::class)
            ->getForm()
        ;
    }
}
