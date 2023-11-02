<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\Repository\ForumGroupRepository;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForumType extends AbstractType
{
    public function __construct(private readonly ForumRepository $forumRepository)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Forum::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $parentId = ($options['data'] ?? null)?->getParent()?->getId();

        $builder
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
            ->add('group', EntityType::class, [
                'class' => ForumGroup::class,
                'choice_label' => 'title',
                'required' => false,
                'autocomplete' => true,
                'query_builder' => $this->getGroupQueryBuilder($parentId),
            ])
            ->add('parent', HiddenType::class, [
                'data' => $parentId,
            ]);

        $builder
            ->get('parent')
            ->addModelTransformer(new CallbackTransformer(
                fn ($parentId) => $parentId,
                fn ($parentId) => !empty($parentId)
                    ? $this->forumRepository->find($parentId)
                    : null,
            ));
    }

    private function getGroupQueryBuilder(?int $parentId): callable
    {
        return function (ForumGroupRepository $repository) use ($parentId): QueryBuilder {
            $qb = $repository
                ->createQueryBuilder('fg')
                ->where('fg.parentForum IS NULL')
                ->orderBy('fg.position', 'ASC');

            if ($parentId !== null) {
                $qb->where('fg.parentForum = :parentId');
                $qb->setParameter('parentId', $parentId);
            }

            return $qb;
        };
    }
}
