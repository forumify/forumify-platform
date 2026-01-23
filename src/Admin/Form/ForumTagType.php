<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Forum\Entity\ForumTag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractType<ForumTag|null>
 */
class ForumTagType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ForumTag::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $forum = ($options['data'] ?? null)?->forum;

        $builder
            ->add('title', TextType::class)
            ->add('color', ColorType::class)
        ;

        if ($forum !== null) {
            $builder->add('allowInSubforums', CheckboxType::class, [
                'required' => false,
                'help' => new TranslatableMessage('admin.forum_tag.crud.allow_in_subforum_help', [
                    'forum' => $forum->getTitle(),
                ]),
            ]);
        }

        $builder->add('default', CheckboxType::class, [
            'required' => false,
            'help' => 'admin.forum_tag.crud.default_help',
        ]);
    }
}
