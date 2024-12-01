<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Forumify\Core\Repository\AbstractRepository;
use RuntimeException;
use Twig\Extension\RuntimeExtensionInterface;

class RepositoryRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return AbstractRepository<object>
     */
    public function getRepository(string $entity): AbstractRepository
    {
        $repository = $this->getDoctrineRepository($entity);
        if (!$repository instanceof AbstractRepository) {
            throw new RuntimeException(get_class($repository) . " must extend " . AbstractRepository::class);
        }

        return $repository;
    }

    /**
     * @return EntityRepository<object>
     */
    private function getDoctrineRepository(string $entity): EntityRepository
    {
        $knownEntities = $this->entityManager->getMetadataFactory()->getAllMetadata();

        // first try to find one by FQCN
        foreach ($knownEntities as $knownEntity) {
            $knownEntityName = $knownEntity->getName();
            if ($knownEntityName === $entity) {
                return $this->entityManager->getRepository($knownEntityName);
            }
        }

        // then try to find one just using classname
        foreach ($knownEntities as $knownEntity) {
            $knownEntityName = $knownEntity->getName();
            $entityClassName = explode('\\', $knownEntityName);
            if (array_pop($entityClassName) === $entity) {
                return $this->entityManager->getRepository($knownEntityName);
            }
        }

        throw new RuntimeException("Unable to find a repository for $entity.");
    }
}
