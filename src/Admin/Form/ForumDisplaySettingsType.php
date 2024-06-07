<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Forum\Entity\ForumDisplaySettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForumDisplaySettingsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ForumDisplaySettings::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('showTopicAuthor', CheckboxType::class, [
                'required' => false,
            ])
            ->add('showTopicStatistics', CheckboxType::class, [
                'required' => false,
            ])
            ->add('showTopicLastCommentBy', CheckboxType::class, [
                'required' => false,
            ])
            ->add('showTopicPreview', CheckboxType::class, [
                'required' => false,
            ])
            ->add('showLastCommentBy', CheckboxType::class, [
                'required' => false,
            ]);
    }
}
