<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;

class TopicWidget extends AbstractWidget
{
    public function __construct(
        private readonly ForumRepository $forumRepository,
        private readonly TopicRepository $topicRepository,
    ) {
    }

    public function getName(): string
    {
        return 'forum.topic';
    }

    public function getCategory(): string
    {
        return 'forum';
    }

    public function getPreview(): string
    {
        return '<div>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Et quod fugit non ut, modi hic vero blanditiis commodi dignissimos ad suscipit aperiam enim dolorem ratione recusandae incidunt eos laboriosam minima.</div>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/topic.html.twig';
    }

    public function getTemplateContext(array $settings): array
    {
        $context = ['topic' => null];

        $forumId = $settings['forum'] ?? null;
        if (is_numeric($forumId)) {
            $context['topic'] = $this->topicRepository->createQueryBuilder('t')
                ->where('t.forum = :forum')
                ->setParameter('forum', $forumId)
                ->orderBy('t.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }

        return $context;
    }

    public function getSettingsForm(array $data = []): ?FormInterface
    {
        $forums = $this->forumRepository->findAll();
        $forumChoices = [];
        foreach ($forums as $forum) {
            $forumChoices[$forum->getTitle()] = $forum->getId();
        }

        return $this->createForm($data)
            ->add('forum', ChoiceType::class, [
                'autocomplete' => true,
                'choices' => $forumChoices,
                'help' => 'admin.cms.widget.forum.topic_forum_help',
            ])
            ->getForm()
        ;
    }
}
