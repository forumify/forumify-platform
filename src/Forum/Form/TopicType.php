<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Forumify\Core\Form\EntityType;
use Forumify\Core\Form\RichTextEditorType;
use Forumify\Core\Form\UploadType;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumTag;
use Forumify\Forum\Repository\ForumTagRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<TopicData>
 */
class TopicType extends AbstractType
{
    public function __construct(
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

        /** @var Forum|null $forum */
        $forum = $options['forum'];
        $forumType = $forum?->getType();

        $builder->add('title', TextType::class);


        $selectableTags = $this->forumTagRepository->findByForum($forum);
        if (!empty($selectableTags)) {
            $builder->add('tags', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'class' => ForumTag::class,
                'choices' => $selectableTags,
                'choice_label' => 'title',
            ]);
        }

        if (in_array($forumType, [Forum::TYPE_IMAGE, Forum::TYPE_MIXED], true)) {
            $builder->add('image', UploadType::class, [
                'required' => $forumType === Forum::TYPE_IMAGE,
                'filesystem' => 'media.storage',
                'asset_package' => 'forumify.media',
                'accept' => 'image/*',
                'file_constraints' => [new Assert\Image(maxSize: '10M')],
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
