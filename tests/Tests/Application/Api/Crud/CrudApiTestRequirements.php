<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Crud;

use LogicException;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Test\Factories;

trait CrudApiTestRequirements
{
    use Factories;

    /** @return class-string<PersistentObjectFactory> */
    abstract protected static function factory(): string;

    abstract protected static function endpoint(): string;

    // FIXME: This covers 99% of cases, could probably get this from entity metadata in ApiPlatform..
    // Not sure if it's worth the time to auto-fetch it.
    protected function getIdentifier(object $entity): mixed
    {
        if (method_exists($entity, 'getId')) {
            return $entity->getId();
        }

        if (property_exists($entity, 'id')) {
            return $entity->id;
        }

        throw new LogicException(sprintf(
            'Unable to get identifier from %s, please overwrite %s to use this test.',
            get_class($entity),
            __FUNCTION__,
        ));
    }
}
