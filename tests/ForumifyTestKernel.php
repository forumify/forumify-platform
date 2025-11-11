<?php

declare(strict_types=1);

namespace Tests;

use Forumify\Core\ForumifyKernel;

class ForumifyTestKernel extends ForumifyKernel
{
    public function __construct(string $env, bool $debug = false)
    {
        $context = ['APP_ENV' => $env, 'APP_DEBUG' => $debug];
        parent::__construct($context, dirname(__DIR__) . '/tests');
    }

    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Forumify\ForumifyBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Twig\Extra\TwigExtraBundle\TwigExtraBundle(),
            new \Symfony\WebpackEncoreBundle\WebpackEncoreBundle(),
            new \Symfony\UX\TwigComponent\TwigComponentBundle(),
            new \Symfony\UX\LiveComponent\LiveComponentBundle(),
            new \Symfony\UX\Autocomplete\AutocompleteBundle(),
            new \Symfony\UX\StimulusBundle\StimulusBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new \League\FlysystemBundle\FlysystemBundle(),
            new \Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \DAMA\DoctrineTestBundle\DAMADoctrineTestBundle(),
            new \ApiPlatform\Symfony\Bundle\ApiPlatformBundle(),
            new \Zenstruck\Foundry\ZenstruckFoundryBundle(),
        ];
    }
}
