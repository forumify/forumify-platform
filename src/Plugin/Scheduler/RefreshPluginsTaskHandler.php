<?php

declare(strict_types=1);

namespace Forumify\Plugin\Scheduler;

use Forumify\Plugin\Service\PluginService;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask('@midnight', jitter: 30)]
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
