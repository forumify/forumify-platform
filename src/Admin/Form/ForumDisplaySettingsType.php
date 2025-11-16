<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Forum\Entity\ForumDisplaySettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
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
                'help' => 'admin.forum.display_settings_help.show_topic_author'
            ])
            ->add('showTopicStatistics', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.forum.display_settings_help.show_topic_statistics'
            ])
            ->add('showTopicLastCommentBy', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.forum.display_settings_help.show_topic_last_comment_by'
            ])
            ->add('showTopicPreview', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.forum.display_settings_help.show_topic_preview'
            ])
            ->add('showLastCommentBy', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.forum.display_settings_help.show_topic_last_comment_by'
            ])
            ->add('onlyShowOwnTopics', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.forum.display_settings_help.only_show_own_topics'
            ]);
    }
}
