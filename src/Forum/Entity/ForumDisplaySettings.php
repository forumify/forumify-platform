<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class ForumDisplaySettings
{
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showTopicAuthor = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showTopicStatistics = true;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showTopicLastCommentBy = true;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $showTopicPreview = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $showLastCommentBy = true;

    public function isShowTopicAuthor(): bool
    {
        return $this->showTopicAuthor;
    }

    public function setShowTopicAuthor(bool $showTopicAuthor): void
    {
        $this->showTopicAuthor = $showTopicAuthor;
    }

    public function isShowTopicStatistics(): bool
    {
        return $this->showTopicStatistics;
    }

    public function setShowTopicStatistics(bool $showTopicStatistics): void
    {
        $this->showTopicStatistics = $showTopicStatistics;
    }

    public function isShowTopicLastCommentBy(): bool
    {
        return $this->showTopicLastCommentBy;
    }

    public function setShowTopicLastCommentBy(bool $showTopicLastCommentBy): void
    {
        $this->showTopicLastCommentBy = $showTopicLastCommentBy;
    }

    public function isShowTopicPreview(): bool
    {
        return $this->showTopicPreview;
    }

    public function setShowTopicPreview(bool $showTopicPreview): void
    {
        $this->showTopicPreview = $showTopicPreview;
    }

    public function isShowLastCommentBy(): bool
    {
        return $this->showLastCommentBy;
    }

    public function setShowLastCommentBy(bool $showLastCommentBy): void
    {
        $this->showLastCommentBy = $showLastCommentBy;
    }
}
