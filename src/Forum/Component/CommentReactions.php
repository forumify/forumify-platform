<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\CommentReaction;
use Forumify\Forum\Entity\Reaction;
use Forumify\Forum\Repository\CommentReactionRepository;
use Forumify\Forum\Repository\ReactionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('CommentReactions', '@Forumify/frontend/components/comment_reactions.html.twig')]
class CommentReactions
{
    use DefaultActionTrait;

    #[LiveProp]
    public Comment $comment;

    #[LiveProp(writable: true)]
    public string $reactionSearch = '';

    public function __construct(
        private readonly CommentReactionRepository $commentReactionRepository,
        private readonly ReactionRepository $reactionRepository,
        private readonly Security $security
    ) {
    }

    #[LiveAction]
    public function toggleReaction(#[LiveArg] int $reactionId, #[LiveArg] bool $allowRemove = true): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if ($user === null) {
            return;
        }

        $reaction = $this->reactionRepository->find($reactionId);
        $commentReaction = $this->commentReactionRepository->findOneBy([
            'comment' => $this->comment,
            'reaction' => $reaction,
            'user' => $user,
        ]);

        if ($commentReaction !== null) {
            if ($allowRemove) {
                $this->commentReactionRepository->remove($commentReaction);
            }
            return;
        }

        $commentReaction = new CommentReaction($this->comment, $user, $reaction);
        $this->commentReactionRepository->save($commentReaction);
    }

    /**
     * @return array<Reaction>
     */
    public function getReactions(): array
    {
        return $this->reactionRepository
            ->createQueryBuilder('r')
            ->where('r.name LIKE :search')
            ->setParameter('search', '%' . $this->reactionSearch . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<array{id: int, name: string, image: string, count: int}>
     */
    public function getGroupedReactions(): array
    {
        return $this->commentReactionRepository
            ->createQueryBuilder('cr')
            ->select('r.id', 'r.name', 'r.image', 'COUNT(cr.id) AS count')
            ->join('cr.reaction', 'r')
            ->where('cr.comment = :commentId')
            ->groupBy('r.id')
            ->setParameter('commentId', $this->comment->getId())
            ->getQuery()
            ->getResult();
    }

    public function hasUserReacted(int $reactionId): bool
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if ($user === null) {
            return false;
        }

        try {
            $count = $this->commentReactionRepository
                ->createQueryBuilder('cr')
                ->select('COUNT(cr.id)')
                ->where('cr.comment = :commentId')
                ->andWhere('cr.user = :userId')
                ->andWhere('cr.reaction = :reactionId')
                ->setParameter('commentId', $this->comment->getId())
                ->setParameter('userId', $user->getId())
                ->setParameter('reactionId', $reactionId)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }

        return $count > 0;
    }
}
