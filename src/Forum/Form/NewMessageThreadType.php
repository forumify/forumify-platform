<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewMessageThreadType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => NewMessageThread::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('participants', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'multiple' => true,
                'autocomplete' => true,
                'query_builder' => $this->addUserFilter(...),
            ])
            ->add('message', TextareaType::class);
    }

    private function addUserFilter(EntityRepository $er): QueryBuilder
    {
        $loggedInUser = $this->security->getUser();
        if ($loggedInUser === null) {
            return $er->createQueryBuilder('u');
        }

        return $er->createQueryBuilder('u')
            ->andWhere('u.username NOT LIKE :currentUsername')
            ->andWhere('u.banned = 0')
            ->andWhere('u.emailVerified = 1')
            ->setParameter('currentUsername', $loggedInUser->getUserIdentifier());
    }
}
