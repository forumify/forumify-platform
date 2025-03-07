<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\FormInterface;

#[AutoconfigureTag('forumify.cms.widget')]
interface WidgetInterface
{
    public function getName(): string;
    public function getCategory(): string;
    public function getPreview(): string;
    public function getTemplate(): string;
    public function getSettingsForm(array $data = []): ?FormInterface;
}
