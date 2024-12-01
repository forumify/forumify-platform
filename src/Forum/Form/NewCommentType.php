<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Forumify\Core\Form\RichTextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<NewComment>
 */
class NewCommentType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewComment::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', RichTextEditorType::class, [
            'label' => $options['label'],
        ]);
    }
}
