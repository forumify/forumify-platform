<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\ACLService;
use Forumify\Core\Service\MediaService;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Form\NewCommentType;
use Forumify\Forum\Form\TopicData;
use Forumify\Forum\Form\TopicType;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Repository\TopicRepository;
use Forumify\Forum\Service\CreateCommentService;
use Forumify\Forum\Service\LastCommentService;
use League\Flysystem\FilesystemOperator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/topic', name: 'topic')]
class TopicController extends AbstractController
{
    public function __construct(
        private readonly CreateCommentService $createCommentService,
        private readonly TopicRepository $topicRepository,
        private readonly LastCommentService $lastCommentService,
        private readonly ReadMarkerRepository $readMarkerRepository,
        private readonly ForumRepository $forumRepository,
        private readonly ACLService $aclService,
    ) {
    }

    #[Route('/{slug:topic}', name: '')]
    public function __invoke(Topic $topic, Request $request): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::TopicView->value, $topic);

        $commentForm = null;
        if ($this->isGranted(VoterAttribute::CommentCreate->value, $topic)) {
            $commentForm = $this->createForm(NewCommentType::class, options: [
                'label' => false,
            ]);

            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid() && !empty($commentForm->getData())) {
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

    #[Route('/{slug:topic}/edit', '_edit')]
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

    #[Route('/{slug:topic}/pin', '_pin')]
    public function pin(Topic $topic): Response
    {
        if (!$this->aclService->can('moderate', $topic->getForum())) {
            throw $this->createAccessDeniedException();
        }

        $topic->setPinned(!$topic->isPinned());
        $this->topicRepository->save($topic);

        return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
    }

    #[Route('/{slug:topic}/toggle-lock', '_lock')]
    public function lock(Topic $topic): Response
    {
        if (!$this->aclService->can('moderate', $topic->getForum())) {
            throw $this->createAccessDeniedException();
        }

        $topic->setLocked(!$topic->isLocked());
        $this->topicRepository->save($topic);

        return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
    }

    #[Route('/{slug:topic}/toggle-visibility', '_hide')]
    public function hide(Topic $topic): Response
    {
        if (!$this->aclService->can('moderate', $topic->getForum())) {
            throw $this->createAccessDeniedException();
        }

        $topic->setHidden(!$topic->isHidden());

        $this->topicRepository->save($topic);
        $this->lastCommentService->clearCache();

        return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
    }

    #[Route('/{slug:topic}/move', '_move')]
    public function move(Topic $topic, Request $request): Response
    {
        if (!$this->aclService->can('moderate', $topic->getForum())) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createFormBuilder($topic, ['data_class' => Topic::class])
            ->add('forum', EntityType::class, [
                'class' => Forum::class,
                'choice_label' => 'title',
                'autocomplete' => true,
                'query_builder' => function () {
                    $qb = $this->forumRepository->createQueryBuilder('e');
                    $this->forumRepository->addACLToQuery($qb, 'moderate');
                    return $qb;
                },
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $topic = $form->getData();
            $this->topicRepository->save($topic);
            $this->lastCommentService->clearCache();

            $this->addFlash('success', 'forum.topic.flashes.topic_moved');
            return $this->redirectToRoute('forumify_forum_topic', ['slug' => $topic->getSlug()]);
        }

        return $this->render('@Forumify/form/simple_form_page.html.twig', [
            'form' => $form->createView(),
            'title' => 'forum.topic.actions.move',
            'cancelPath' => $this->generateUrl('forumify_forum_topic', ['slug' => $topic->getSlug()]),
        ]);
    }

    #[Route('/{slug:topic}/delete', '_delete')]
    public function delete(Request $request, Topic $topic): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::TopicDelete->value, $topic);

        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/frontend/forum/topic_delete.html.twig', [
                'topic' => $topic,
            ]);
        }

        $forum = $topic->getForum();
        $this->topicRepository->remove($topic);
        $this->lastCommentService->clearCache();

        $this->addFlash('success', 'flashes.topic_removed');
        return $this->redirectToRoute('forumify_forum_forum', ['slug' => $forum->getSlug()]);
    }
}
