<?php

declare(strict_types=1);

namespace Forumify\Plugin\Twig\Extension;

use Forumify\Core\Service\ThemeService;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ThemeExtension extends AbstractExtension
{
    public function __construct(
        private readonly ThemeService $themeService,
        private readonly Packages $packages,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('theme_tags', $this->getThemeTags(...), ['needs_context' => true, 'is_safe' => ['html']]),
        ];
    }

    public function getThemeTags(array $context): string
    {
        $metaData = $this->themeService->getThemeMetaData();
        $lastModified = $metaData['lastModified'] ?? '0';

        /** @var AppVariable $app */
        $app = $context['app'];
        $preference = $app->getRequest()->cookies->get(ThemeService::CURRENT_THEME_COOKIE) ?? 'system';

        $links = [
            $this->packages->getUrl(sprintf(ThemeService::THEME_FILE_FORMAT, 'custom', $lastModified), 'forumify.asset'),
            $this->packages->getUrl(sprintf(ThemeService::THEME_FILE_FORMAT, $preference, $lastModified), 'forumify.asset'),
        ];
        foreach ($metaData['stylesheets'] ?? [] as $stylesheet) {
            $links[] = $this->packages->getUrl('themes/' . $stylesheet);
        }

        return array_reduce(
            $links,
            static fn (string $acc, string $link) => "$acc<link rel='stylesheet' href='$link'>",
            ''
        );
    }
}
