<?php
declare(strict_types=1);

namespace Tests\Tests\Traits;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait RequiresContainerTrait
{
    abstract protected static function getContainer(): ContainerInterface;
}
