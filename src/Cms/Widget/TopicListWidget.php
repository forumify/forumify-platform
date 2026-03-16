<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Forumify\Forum\Component\TopicList;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumDisplaySettings;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TopicListWidget extends AbstractWidget
{
    public function __construct(
        private readonly ForumRepository $forumRepository,
        private readonly SerializerInterface&NormalizerInterface&DenormalizerInterface $serializer,
    ) {
    }

    public function getName(): string
    {
        return 'forum.topic_list';
    }

    public function getCategory(): string
    {
        return 'forum';
    }

    public function getPreview(): string
    {
        return '<div>
            <h3>My Forum</h3>
            <div class="card">
                <p class="card-body">Topic 1</p>
                <hr/>
                <p class="card-body">Topic 2</p>
            </div>
            <button type="button" class="btn-link mt-2">
                <i class="ph ph-arrow-right"></i>View All
            </button>
        </div>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/topic_list.html.twig';
    }

    public function getTemplateContext(array $settings): array
    {
        $forumId = $settings['forum'] ?? null;
        if ($forumId === null || !is_numeric($forumId)) {
            return [];
        }

        /** @var Forum|null $forum */
        $forum = $this->forumRepository->find($forumId);
        if ($forum !== null) {
            $this->normalizeSettings($settings);
            $displaySettings = $this->serializer->denormalize(
                $settings,
                ForumDisplaySettings::class,
                'array',
                ['disable_type_enforcement' => true],
            );
            $forum->setDisplaySettings($displaySettings);
        }

        return ['forum' => $forum];
    }

    public function getSettingsForm(array $data = []): ?FormInterface
    {
        $forums = $this->forumRepository->findAll();
        $forumChoices = [];
        foreach ($forums as $forum) {
            $forumChoices[$forum->getTitle()] = $forum->getId();
        }

        $sortModeOptions = [];
        foreach (TopicList::getAvailableSortModes() as $mode) {
            $sortModeOptions['forum.topic.sort_mode.' . $mode['mode']] = $mode['mode'];
        }

        $this->normalizeSettings($data);
        return $this->createForm($data)
            ->add('forum', ChoiceType::class, [
                'autocomplete' => true,
                'choices' => $forumChoices,
            ])
            ->add('topicCount', NumberType::class)
            ->add('sortMode', ChoiceType::class, [
                'choices' => $sortModeOptions,
            ])
            ->add('showTopicAuthor', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.forum.display_settings_help.show_topic_author',
            ])
            ->add('showTopicStatistics', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.forum.display_settings_help.show_topic_statistics',
            ])
            ->add('showTopicLastCommentBy', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.forum.display_settings_help.show_topic_last_comment_by',
            ])
            ->add('showTopicPreview', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.forum.display_settings_help.show_topic_preview',
            ])
            ->add('showLastCommentBy', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.forum.display_settings_help.show_topic_last_comment_by',
            ])
            ->getForm()
        ;
    }

    private function normalizeSettings(array &$settings): void
    {
        $displaySettingKeys = [
            'showTopicAuthor',
            'showTopicStatistics',
            'showTopicLastCommentBy',
            'showTopicPreview',
            'showLastCommentBy',
        ];
        foreach ($displaySettingKeys as $k) {
            $settings[$k] = (bool)($settings[$k] ?? false);
        }

        $settings['onlyShowOwnTopics'] = false;
        $settings['topicCount'] = (int)($settings['topicCount'] ?? 6);
        $settings['sortMode'] = $settings['sortMode'] ?? 'created_at';
    }
}
