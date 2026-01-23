<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Forumify\Core\Form\EntityType;
use Forumify\Core\Form\RichTextEditorType;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumTag;
use Forumify\Forum\Repository\ForumTagRepository;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<TopicData>
 */
class TopicType extends AbstractType
{
    public function __construct(
        private readonly Packages $packages,
        private readonly ForumTagRepository $forumTagRepository,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'forum' => null,
            'data_class' => TopicData::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var TopicData|null $topicData  */
        $topicData = $options['data'] ?? null;
        $imagePreview = $topicData?->getExistingImage();

        /** @var Forum|null $forum */
        $forum = $options['forum'];
        $forumType = $forum?->getType();

        $builder
            ->add('title', TextType::class)
            ->add('tags', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'class' => ForumTag::class,
                'choices' => $this->forumTagRepository->findByForum($forum),
                'choice_label' => 'title',
            ])
        ;

        if (in_array($forumType, [Forum::TYPE_IMAGE, Forum::TYPE_MIXED], true)) {
            $builder->add('image', FileType::class, [
                'required' => $forumType === Forum::TYPE_IMAGE,
                'attr' => [
                    'preview' => $imagePreview
                        ? $this->packages->getUrl($imagePreview, 'forumify.media')
                        : null,
                ],
            ]);
        }

        if ($topicData !== null) {
            return;
        }

        $template = $forum?->getTopicTemplate() ?? '';
        $builder->add('content', RichTextEditorType::class, [
            'data' => $template,
        ]);
    }
}
