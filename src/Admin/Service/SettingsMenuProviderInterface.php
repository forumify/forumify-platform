<?php

declare(strict_types=1);

namespace Forumify\Admin\Service;

use Forumify\Core\MenuBuilder\Menu;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

interface SettingsMenuProviderInterface
{
    public function provide(UrlGeneratorInterface $u, TranslatorInterface $t): Menu;
}
