<?php

declare(strict_types=1);

namespace Forumify\Core\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RichTextEditorType extends AbstractType
{
    public function getParent(): string
    {
        return TextareaType::class;
    }
}
