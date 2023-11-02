<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Forumify\Core\Form\MarkdownType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewComment::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', MarkdownType::class, [
            'label' => $options['label'],
        ]);
    }
}
