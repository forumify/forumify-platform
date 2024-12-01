<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACLParameters;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ACLExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('acl_parameters', $this->aclParameters(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('can', [ACLRuntime::class, 'canAccess']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function aclParameters(object $object): array
    {
        if (!$object instanceof AccessControlledEntityInterface) {
            throw new \InvalidArgumentException(sprintf(
                'To retrieve ACL parameters, %s must implement %s',
                get_class($object),
                AccessControlledEntityInterface::class
            ));
        }

        return (array)$object->getACLParameters();
    }
}
