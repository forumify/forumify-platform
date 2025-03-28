<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\MediaService;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Form\NewCommentType;
use Forumify\Forum\Form\TopicData;
use Forumify\Forum\Form\TopicType;
use Forumify\Forum\Repository\TopicRepository;
use Forumify\Forum\Service\CreateCommentService;
use Forumify\Forum\Service\LastCommentService;
use League\Flysystem\FilesystemOperator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/topic', name: 'topic')]
class TopicController extends AbstractController
{
    public function __construct(
        private readonly CreateCommentService $createCommentService,
        private readonly TopicRepository $topicRepository,
        private readonly LastCommentService $lastCommentService,
        private readonly ReadMarkerRepository $readMarkerRepository,
    ) {
    }

    #[Route('/{slug}', name: '')]
    public function __invoke(Topic $topic, Request $request): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::TopicView->value, $topic);

        $commentForm = null;
        if ($this->isGranted(VoterAttribute::CommentCreate->value, $topic)) {
            $commentForm = $this->createForm(NewCommentType::class, options: [
                'label' => false,
            ]);

            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $this->createCommentService->createComment($topic, $commentForm->getData());
                return $this->redirectToRoute('forumify_forum_topic', [
                    'slug' => $topic->getSlug(),
                    'lastPageFirst' => true,
                ]);
            }
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $this->readMarkerRepository->read($user, Topic::class, $topic->getId());
        }

        $this->topicRepository->incrementViews($topic);
        return $this->render('@Forumify/frontend/forum/topic.html.twig', [
            'topic' => $topic,
            'commentForm' => $commentForm?->createView(),
        ]);
    }

    #[Route('/{slug}/edit', '_edit')]
    public function edit(
        Request $request,
        Topic $topic,
        FilesystemOperator $mediaStorage,
        MediaService $mediaService,
    ): Response {
        $this->denyAccessUnlessGranted(VoterAttribute::TopicEdit->value, $topic);

        $topicData = new TopicData();
        $topicData->setTitle($topic->getTitle());
        $topicData->setExistingImage($topic->getImage());

        $form = $this->createForm(TopicType::class, $topicData, ['forum' => $topic->getForum()]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TopicData $topicData */
            $topicData = $form->getData();

            $topic->setTitle($topicData->getTitle());
            if ($topicData->getImage() !== null) {
                $newImage = $mediaService->saveToFilesystem($mediaStorage, $topicData->getImage());
                $topic->setImage($newImage);
            }

            $this->topicRepository->save($topic);

            $this->addFlash('success', 'flashes.topic_saved');
            return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
        }

        return $this->render('@Forumify/frontend/forum/topic_edit.html.twig', [
            'topic' => $topic,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}/pin', '_pin')]
    #[IsGranted(VoterAttribute::Moderator->value, new Expression('args["topic"]'))]
    public function pin(Topic $topic): Response
    {
        $topic->setPinned(!$topic->isPinned());
        $this->topicRepository->save($topic);

        return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
    }

    #[Route('/{slug}/toggle-lock', '_lock')]
    #[IsGranted(VoterAttribute::Moderator->value, new Expression('args["topic"]'))]
    public function lock(Topic $topic): Response
    {
        $topic->setLocked(!$topic->isLocked());
        $this->topicRepository->save($topic);

        return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
    }

    #[Route('/{slug}/toggle-visibility', '_hide')]
    #[IsGranted(VoterAttribute::Moderator->value, new Expression('args["topic"]'))]
    public function hide(Topic $topic): Response
    {
        $topic->setHidden(!$topic->isHidden());

        $this->topicRepository->save($topic);
        $this->lastCommentService->clearCache();

        return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
    }

    #[Route('/{slug}/move', '_move')]
    #[IsGranted(VoterAttribute::Moderator->value, new Expression('args["topic"]'))]
    public function move(Topic $topic, Request $request): Response
    {
        $form = $this->createFormBuilder($topic, ['data_class' => Topic::class])
            ->add('forum', EntityType::class, [
                'class' => Forum::class,
                'choice_label' => 'title',
                'autocomplete' => true,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $topic = $form->getData();
            if ($this->isGranted(VoterAttribute::Moderator->value, $topic->getForum())) {
                $this->topicRepository->save($topic);
                $this->lastCommentService->clearCache();

                $this->addFlash('success', 'forum.topic.flashes.topic_moved');
                return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
            }
        }

        return $this->render('@Forumify/form/simple_form_page.html.twig', [
            'form' => $form->createView(),
            'title' => 'forum.topic.actions.move',
            'cancelPath' => $this->generateUrl('forumify_forum_topic', ['slug' => $topic->getSlug()]),
        ]);
    }

    #[Route('/{slug}/delete', '_delete')]
    public function delete(Topic $topic): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::TopicDelete->value, $topic);

        $forum = $topic->getForum();
        $this->topicRepository->remove($topic);
        $this->lastCommentService->clearCache();

        $this->addFlash('success', 'flashes.topic_removed');
        return $this->redirectToRoute('forumify_forum_forum', ['slug' => $forum->getSlug()]);
    }
}
