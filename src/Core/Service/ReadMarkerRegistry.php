<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Maps read marker subjects onto the service that knows how to read them.
 *
 * Subjects are identified by a type and an id instead of their class, so the pair can safely travel
 * to the browser and back without exposing entity classes or allowing arbitrary ones to be loaded.
 */
class ReadMarkerRegistry
{
    /**
     * @param iterable<ReadMarkerServiceInterface<object>> $readMarkerServices
     */
    public function __construct(
        #[AutowireIterator('forumify.read_marker.service')]
        private readonly iterable $readMarkerServices,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return ReadMarkerServiceInterface<object>
     */
    public function getServiceForSubject(object $subject): ReadMarkerServiceInterface
    {
        foreach ($this->readMarkerServices as $service) {
            // subjects can be doctrine proxies, so the class cannot be compared directly
            if (is_a($subject, $service::getEntityClass())) {
                return $service;
            }
        }

        throw new InvalidArgumentException('No read marker service exists for subject ' . $subject::class);
    }

    /**
     * @return ReadMarkerServiceInterface<object>|null
     */
    public function getServiceForType(string $type): ?ReadMarkerServiceInterface
    {
        foreach ($this->readMarkerServices as $service) {
            if ($service::getType() === $type) {
                return $service;
            }
        }

        return null;
    }

    public function getType(object $subject): string
    {
        return $this->getServiceForSubject($subject)::getType();
    }

    public function getId(object $subject): int
    {
        $identifiers = $this->em->getClassMetadata($subject::class)->getIdentifierValues($subject);
        if (count($identifiers) !== 1) {
            throw new InvalidArgumentException('Read marker subjects must have a single identifier.');
        }

        return (int)reset($identifiers);
    }

    /**
     * @param array<int> $ids
     * @return array<int, object> keyed by id
     */
    public function findSubjects(string $type, array $ids): array
    {
        $service = $this->getServiceForType($type);
        if ($service === null || empty($ids)) {
            return [];
        }

        $subjects = $this->em->getRepository($service::getEntityClass())->findBy(['id' => $ids]);

        $subjectsById = [];
        foreach ($subjects as $subject) {
            $subjectsById[$this->getId($subject)] = $subject;
        }

        return $subjectsById;
    }
}
