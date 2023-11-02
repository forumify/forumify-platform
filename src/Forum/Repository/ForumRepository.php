<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Forumify\Forum\Entity\Forum;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ForumRepository extends NestedTreeRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(Forum::class));
    }

    /**
     * @return array<Forum>
     */
    public function findByParent(?Forum $parent): array
    {
        return $this->findBy(['parent' => $parent]);
    }

    /**
     * @return array<Forum>
     */
    public function findUngroupedByParent(?Forum $parent): array
    {
        return $this->findBy(['parent' => $parent, 'group' => null]);
    }

    public function save(object $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(object $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
