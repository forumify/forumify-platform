<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Form\RichTextEditorType;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumDisplaySettings;
use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\Repository\ForumGroupRepository;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            'is_new' => false,
            'data_class' => Forum::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $parentId = ($options['data'] ?? null)?->getParent()?->getId();

        $builder
            ->add('title', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'admin.forum.type.text' => Forum::TYPE_TEXT,
                    'admin.forum.type.image' => Forum::TYPE_IMAGE,
                    'admin.forum.type.mixed' => Forum::TYPE_MIXED,
                    'admin.forum.type.support' => Forum::TYPE_SUPPORT,
                ],
                'placeholder' => 'admin.forum.type_placeholder',
                'help' => 'admin.forum.type_help',
                'help_html' => true,
            ])
            ->add('content', RichTextEditorType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('group', EntityType::class, [
                'class' => ForumGroup::class,
                'choice_label' => 'title',
                'required' => false,
                'autocomplete' => true,
                'query_builder' => $this->getGroupQueryBuilder($parentId),
            ])
            ->add('parent', HiddenType::class, [
                'data' => $parentId,
            ])
            ->add('topicTemplate', RichTextEditorType::class, [
                'required' => false,
                'help' => 'admin.forum.topic_template_help'
            ]);

        if (!$options['is_new']) {
            $builder->add('displaySettings', ForumDisplaySettingsType::class);
        }

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
