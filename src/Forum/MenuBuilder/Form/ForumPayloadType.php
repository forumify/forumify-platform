<?php

declare(strict_types=1);

namespace Forumify\Forum\MenuBuilder\Form;

use Forumify\Forum\Repository\ForumRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<mixed>>
 */
class ForumPayloadType extends AbstractType
{
    public function __construct(private readonly ForumRepository $forumRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = ['root' => ''];
        foreach ($this->forumRepository->findAll() as $forum) {
            $label = "{$forum->getTitle()} ({$forum->getSlug()})";
            $choices[$label] = $forum->getSlug();
        }

        $builder->add('forum', ChoiceType::class, [
            'choices' => $choices,
            'required' => false,
            'empty_data' => '',
        ]);
    }
}
