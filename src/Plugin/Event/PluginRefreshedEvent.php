<?php

declare(strict_types=1);

namespace Forumify\Plugin\Event;

use Forumify\Plugin\Entity\Plugin;
use Symfony\Contracts\EventDispatcher\Event;

class PluginRefreshedEvent extends Event
{
    public function __construct(public readonly Plugin $plugin)
    {
    }
}
