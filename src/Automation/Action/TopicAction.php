<?php

declare(strict_types=1);

namespace Forumify\Automation\Action;

use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Form\TopicActionType;
use Forumify\Automation\Service\UserExpressionResolver;
use Forumify\Forum\Form\TopicData;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Service\CreateTopicService;
use Twig\Environment;

class TopicAction implements ActionInterface
{
    public function __construct(
        private readonly UserExpressionResolver $userExpressionResolver,
        private readonly ForumRepository $forumRepository,
        private readonly Environment $twig,
        private readonly CreateTopicService $createTopicService,
    ) {
    }

    public static function getType(): string
    {
        return 'Post Topic';
    }

    public function getPayloadFormType(): ?string
    {
        return TopicActionType::class;
    }

    /**
     * @param Automation $automation
     * @param array<string, mixed>|null $payload
     * @return void
     */
    public function run(Automation $automation, ?array $payload): void
    {
        [
            'forum' => $forumId,
            'author' => $authorExpr,
            'title' => $title,
            'content' => $content,
        ] = $automation->getActionArguments();

        $forum = $this->forumRepository->find($forumId);
        if ($forum === null) {
            return;
        }

        $authors = $this->userExpressionResolver->resolve($authorExpr, $payload);
        $author = reset($authors);
        if ($author === false) {
            return;
        }

        $title = $this->twig->createTemplate($title)->render($payload ?? []);
        $content = $this->twig->createTemplate($content)->render($payload ?? []);

        $topic = new TopicData();
        $topic->setTitle($title);
        $topic->setContent($content);
        $topic->setAuthor($author);

        $this->createTopicService->createTopic($forum, $topic);
    }
}
