<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Badge;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<User>
 */
class UserType extends AbstractType
{
    public function __construct(
        private readonly Packages $packages,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['data'];

        $builder
            ->add('username', TextType::class)
            ->add('displayName', TextType::class)
            ->add('email', TextType::class)
            ->add('timezone', TimezoneType::class, ['autocomplete' => true])
            ->add('newAvatar', FileType::class, [
                'mapped' => false,
                'label' => 'Avatar',
                'attr' => [
                    'preview' => !empty($user->getAvatar())
                        ? $this->packages->getUrl($user->getAvatar(), 'forumify.avatar')
                        : null,
                ],
                'constraints' => [
                    new Assert\Image(
                        maxSize: '10M',
                    ),
                ],
            ])
            ->add('roleEntities', UserRoleType::class, [
                'label' => 'Roles',
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
            ])
            ->add('badges', EntityType::class, [
                'class' => Badge::class,
                'choice_label' => 'name',
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
            ]);
    }
}
