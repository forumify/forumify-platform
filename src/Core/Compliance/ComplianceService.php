<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance;

use Forumify\Cms\Entity\Page;
use Forumify\Cms\Repository\PageRepository;
use Forumify\Core\Repository\SettingRepository;

class ComplianceService
{
    public const string SETTING_MODE = 'forumify.compliance.mode';
    public const string SETTING_CMS_PAGE = 'forumify.compliance.cms_page';
    public const string SETTING_LAST_UPDATED = 'forumify.compliance.last_updated';
    public const string SETTING_PREFIX = 'forumify.compliance.';

    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly PageRepository $pageRepository,
    ) {
    }

    public function getMode(): ComplianceMode
    {
        $mode = $this->settingRepository->get(self::SETTING_MODE);
        if (!is_string($mode)) {
            return ComplianceMode::Off;
        }

        return ComplianceMode::tryFrom($mode) ?? ComplianceMode::Off;
    }

    public function getCmsPage(): ?Page
    {
        $pageId = $this->settingRepository->get(self::SETTING_CMS_PAGE);
        if (!is_numeric($pageId)) {
            return null;
        }

        return $this->pageRepository->find((int)$pageId);
    }

    public function isEnabled(): bool
    {
        return match ($this->getMode()) {
            ComplianceMode::Off => false,
            ComplianceMode::Generated => true,
            ComplianceMode::CmsPage => $this->getCmsPage() !== null,
        };
    }
}
