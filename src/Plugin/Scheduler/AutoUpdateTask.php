<?php

declare(strict_types=1);

namespace Forumify\Plugin\Scheduler;

use Forumify\Core\Repository\SettingRepository;
use Forumify\Plugin\Application\Service\PluginService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask('0 5 * * MON')]
class AutoUpdateTask
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        #[Autowire(env: 'DATABASE_URL')]
        private readonly string $databaseUrl,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $rootDir,
    ) {
    }

    public function __invoke(): void
    {
        $autoUpdateEnabled = $this->settingRepository->get('forumify.enable_auto_updates');
        if (!$autoUpdateEnabled) {
            return;
        }


        $pluginService = new PluginService([
            'DATABASE_URL' => $this->databaseUrl,
            'DOCUMENT_ROOT' => $this->rootDir . DIRECTORY_SEPARATOR . 'index.php',
        ]);

        $pluginService->updateAll();
    }
}
