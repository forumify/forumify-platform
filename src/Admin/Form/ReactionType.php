<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Form\UploadType;
use Forumify\Forum\Entity\Reaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Reaction>
 */
class ReactionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reaction::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('image', UploadType::class, [
                'label' => 'Image',
                'help' => 'Recommended size is 64x64.',
                'filesystem' => 'asset.storage',
                'asset_package' => 'forumify.asset',
                'accept' => 'image/*',
                'file_constraints' => [new Assert\Image(maxSize: '10M')],
            ])
            ->add('reputation', ChoiceType::class, [
                'choices' => [
                    'Neutral' => 0,
                    'Positive' => 1,
                    'Negative' => -1,
                ],
            ])
        ;
    }
}
