<?php

declare(strict_types=1);

namespace Forumify\Core\Component;

use Doctrine\ORM\EntityManagerInterface;
use Forumify\Core\Entity\User;
use Forumify\Core\Service\ReadMarkerServiceInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;

#[AsLiveComponent('ReadMarker', '@Forumify/components/read_marker.html.twig')]
class ReadMarker
{
    use ComponentToolsTrait;

    #[LiveProp(hydrateWith: 'hydrateItem', dehydrateWith: 'dehydrateItem')]
    public object $item;

    public function __construct(
        private readonly EntityManagerInterface $em,
        /**
         * @var iterable<ReadMarkerServiceInterface>
         */
        #[AutowireIterator('forumify.read_marker.service')]
        private readonly iterable $readMarkerServices,
        private readonly Security $security,
    ) {
    }

    #[LiveListener('forumify.read_markers.updated')]
    public function __invoke(): void
    {
    }

    public function hydrateItem(array $itemData): object
    {
        ['class' => $class, 'ids' => $ids] = $itemData;
        return $this->em->getRepository($class)->findOneBy($ids);
    }

    public function dehydrateItem(object $item): array
    {
        $propertyAccessor = new PropertyAccessor();
        $class = get_class($item);
        $identifiers = $this->em->getClassMetadata($class)->getIdentifier();

        $ids = [];
        foreach ($identifiers as $identifier) {
            $ids[$identifier] = $propertyAccessor->getValue($item, $identifier);
        }

        return [
            'class' => $class,
            'ids' => $ids,
        ];
    }

    #[LiveAction]
    #[IsGranted('ROLE_USER')]
    public function markAsRead(): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        foreach ($this->readMarkerServices as $readMarkerService) {
            if ($readMarkerService->supports($this->item)) {
                $readMarkerService->markAsRead($user, $this->item);
                break;
            }
        }

        $this->emit('forumify.read_markers.updated');
    }
}
