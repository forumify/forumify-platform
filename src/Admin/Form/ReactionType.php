<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Forum\Entity\Reaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReactionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver):void
    {
        $resolver->setDefaults([
            'data_class' => Reaction::class
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('newImage', FileType::class, [
                'mapped' => false,
                'required' => false,
                'help' => 'Recommended size is 64x64.',
                'constraints' => [
                    new Assert\Image(
                        maxSize: '10M',
                    ),
                ],
            ]);
    }
}
