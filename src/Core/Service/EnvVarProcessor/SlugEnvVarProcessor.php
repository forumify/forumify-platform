<?php

declare(strict_types=1);

namespace Forumify\Core\Service\EnvVarProcessor;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class SlugEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv(string $prefix, string $name, \Closure $getEnv): mixed
    {
        $env = $getEnv($name);
        return (new AsciiSlugger())->slug($env)->toString();
    }

    public static function getProvidedTypes(): array
    {
        return [
            'slug' => 'string',
        ];
    }
}
