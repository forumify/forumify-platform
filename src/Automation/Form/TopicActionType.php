<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Forumify\Core\Form\CodeEditorLanguage;
use Forumify\Core\Form\CodeEditorType;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class TopicActionType extends AbstractType
{
    public function __construct(private readonly ForumRepository $forumRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<Forum> $forums */
        $forums = $this->forumRepository->findAll();

        $forumChoices = [];
        foreach ($forums as $forum) {
            $forumChoices[$forum->getTitle() . ' (' . $forum->getSlug() . ')'] = $forum->getId();
        }

        $builder
            ->add('forum', ChoiceType::class, [
                'autocomplete' => true,
                'choices' => $forumChoices,
                'placeholder' => 'admin.automations.action.topic.forum_placeholder',
            ])
            ->add('author', CodeEditorType::class, [
                'help' => 'admin.automations.action.topic.author_help',
                'help_html' => true,
                'density' => 'compact',
            ])
            ->add('title', CodeEditorType::class, [
                'language' => CodeEditorLanguage::Twig->value,
                'help' => 'admin.automations.action.topic.title_help',
                'help_html' => true,
                'density' => 'compact',
            ])
            ->add('content', CodeEditorType::class, [
                'language' => CodeEditorLanguage::Twig->value,
                'help' => 'admin.automations.action.topic.content_help',
                'help_html' => true,
            ])
        ;
    }
}
