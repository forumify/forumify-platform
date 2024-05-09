<?php

declare(strict_types=1);

namespace Forumify\Plugin\Scheduler;

use Forumify\Plugin\Service\PluginService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: RefreshPluginsTask::class)]
class RefreshPluginsTaskHandler
{
    public function __construct(private readonly PluginService $pluginService)
    {
    }

    public function __invoke(): void
    {
        $this->pluginService->refresh();
    }
}
