<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class InstanceOfExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            new TwigTest('instanceof', [$this, 'instanceof'])
        ];
    }

    public function instanceof($object, string $classname): bool
    {
        if (!is_object($object)) {
            return false;
        }
        return $object instanceof $classname;
    }
}
