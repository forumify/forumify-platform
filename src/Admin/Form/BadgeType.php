<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Form\UploadType;
use Forumify\Forum\Entity\Badge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Badge>
 */
class BadgeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Badge::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class, [
                'empty_data' => '',
                'required' => false,
            ])
            ->add('image', UploadType::class, [
                'label' => 'Image',
                'help' => 'Recommended size is 64x64.',
                'filesystem' => 'asset.storage',
                'asset_package' => 'forumify.asset',
                'accept' => 'image/*',
                'file_constraints' => [new Assert\Image(maxSize: '10M')],
            ])
            ->add('showOnForum', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }
}
