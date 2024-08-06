<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Badge;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function __construct(
        private readonly Packages $packages,
        private readonly Security $security,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_super_admin' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['data'] ?? null;

        $builder
            ->add('username', TextType::class)
            ->add('email', TextType::class)
            ->add('newAvatar', FileType::class, [
                'mapped' => false,
                'label' => 'Avatar',
                'attr' => [
                    'preview' => $user !== null && !empty($user->getAvatar())
                        ? $this->packages->getUrl($user->getAvatar(), 'forumify.avatar')
                        : null,
                ],
                'constraints' => [
                    new Assert\Image(
                        maxSize: '10M',
                    ),
                ],
            ])
            ->add('roleEntities', EntityType::class, [
                'class' => Role::class,
                'label' => 'Roles',
                'choice_label' => 'title',
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'query_builder' => $this->getRoleQueryBuilder(...),
                'disabled' => $options['is_super_admin'] && !$this->security->isGranted('ROLE_SUPER_ADMIN'),
            ])
            ->add('badges', EntityType::class, [
                'class' => Badge::class,
                'choice_label' => 'name',
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
            ]);
    }

    private function getRoleQueryBuilder(EntityRepository $er): QueryBuilder
    {
        return $er
            ->createQueryBuilder('r')
            ->andWhere('r.slug != :guest')
            ->andWhere('r.slug != :user')
            ->setParameters([
                'guest' => 'guest',
                'user' => 'user',
            ]);
    }
}
