<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Badge;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
        $user = $options['data'];

        $builder
            ->add('username', TextType::class)
            ->add('displayName', TextType::class)
            ->add('email', TextType::class)
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
            ->add('roleEntities', EntityType::class, [
                'class' => Role::class,
                'label' => 'Roles',
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'options_as_html' => true,
                'choice_label' => $this->getChoiceLabel(...),
                'choice_attr' => $this->getChoiceAttributes(...),
                'query_builder' => $this->getRoleQueryBuilder(...),
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
            ->orderBy('r.position', 'ASC')
            ->setParameters([
                'guest' => 'guest',
                'user' => 'user',
            ]);
    }

    private function getChoiceLabel(Role $role): string
    {
        $title = $role->getTitle();
        if ($this->security->isGranted(VoterAttribute::AssignRole->value, $role)) {
            return $title;
        }

        return "<div class='disabled'>$title</div>";
    }

    private function getChoiceAttributes(Role $role): array
    {
        if ($this->security->isGranted(VoterAttribute::AssignRole->value, $role)) {
            return [];
        }

        return ['disabled' => 'disabled'];
    }
}
