<?php

declare(strict_types=1);

namespace Forumify\Core\Service\EnvVarProcessor;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class HostnameEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv(string $prefix, string $name, \Closure $getEnv): mixed
    {
        $env = $getEnv($name);
        $url = parse_url($env);

        $host = $url['host'];
        if (isset($url['port'])) {
            $host .= ':' . $url['port'];
        }
        return $host;
    }

    public static function getProvidedTypes(): array
    {
        return [
            'hostname' => 'string',
        ];
    }
}
