<?php

declare(strict_types=1);

namespace Forumify\Core\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Forumify\Core\Api\Dto\MarkAsRead;
use Forumify\Core\Api\Dto\ReadMarkerReference;
use Forumify\Core\Api\Dto\ReadMarkerState;
use Forumify\Core\Api\Resource\ReadMarker;
use Forumify\Core\Entity\User;
use Forumify\Core\Service\ReadMarkerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<MarkAsRead, ReadMarker>
 */
class MarkAsReadProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ReadMarkerRegistry $registry,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ReadMarker
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $this->markAsRead($user, $data->subject);

        return new ReadMarker($this->getMarkerStates($user, $data->markers));
    }

    private function markAsRead(User $user, ?ReadMarkerReference $subject): void
    {
        $entity = $subject !== null
            ? $this->registry->findSubjects($subject->type, [$subject->id])[$subject->id] ?? null
            : null;

        if ($entity === null) {
            throw new NotFoundHttpException('Read marker subject not found.');
        }

        $this->registry->getServiceForSubject($entity)->markAsRead($user, $entity);
    }

    /**
     * @param array<ReadMarkerReference> $markers
     * @return array<ReadMarkerState>
     */
    private function getMarkerStates(User $user, array $markers): array
    {
        $idsPerType = [];
        foreach ($markers as $marker) {
            $idsPerType[$marker->type][] = $marker->id;
        }

        $states = [];
        foreach ($idsPerType as $type => $ids) {
            $service = $this->registry->getServiceForType($type);
            if ($service === null) {
                continue;
            }

            $subjects = $this->registry->findSubjects($type, $ids);
            $service->preload($user, $subjects);

            foreach ($subjects as $id => $subject) {
                $states[] = ReadMarkerState::create($type, $id, $service->read($user, $subject));
            }
        }

        return $states;
    }
}
