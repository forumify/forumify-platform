<?php

declare(strict_types=1);

namespace Forumify\Core;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;

class ForumifyKernel extends Kernel
{
    use MicroKernelTrait {
        MicroKernelTrait::registerBundles as private registerSymfonyBundles;
    }

    private readonly string $projectDir;

    public function __construct(array $context, ?string $projectDir = null)
    {
        parent::__construct($context['APP_ENV'], (bool)$context['APP_DEBUG']);
        $this->projectDir = $projectDir ?? dirname($context['DOCUMENT_ROOT']);
    }

    public function registerBundles(): iterable
    {
        yield from $this->registerSymfonyBundles();

        try {
            $connection = DriverManager::getConnection([
                'url' => $_SERVER['DATABASE_URL'],
            ]);

            $plugins = $connection
                ->executeQuery('SELECT plugin_class FROM plugin WHERE active = 1')
                ->fetchFirstColumn();
        } catch (Exception $ex) {
            $plugins = [];
        }

        foreach ($plugins as $plugin) {
            yield new $plugin();
        }
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }
}
