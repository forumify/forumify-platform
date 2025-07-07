<?php

declare(strict_types=1);

namespace Forumify\Cms\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACLParameters;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SluggableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Cms\Repository\PageRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[UniqueEntity('urlKey')]
class Page implements AccessControlledEntityInterface
{
    public const TYPE_TWIG = 'twig';
    public const TYPE_BUILDER = 'builder';

    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;
    use SluggableEntityTrait;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255, unique: true)]
    private string $urlKey = '';

    #[ORM\Column(type: 'text')]
    private string $seoDescription = '';

    #[ORM\Column(length: 255)]
    private string $seoKeywords = '';

    #[ORM\Column(options: ['default' => self::TYPE_TWIG])]
    private string $type = 'twig';

    #[ORM\Column(type: 'text')]
    private string $twig = '';

    #[ORM\Column(type: 'text')]
    private string $css = '';

    #[ORM\Column(type: 'text')]
    private string $javascript = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getUrlKey(): string
    {
        return $this->urlKey;
    }

    public function setUrlKey(string $urlKey): void
    {
        $this->urlKey = $urlKey;
    }

    public function getSeoDescription(): string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(string $seoDescription): void
    {
        $this->seoDescription = $seoDescription;
    }

    public function getSeoKeywords(): string
    {
        return $this->seoKeywords;
    }

    public function setSeoKeywords(string $seoKeywords): void
    {
        $this->seoKeywords = $seoKeywords;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getTwig(): string
    {
        return $this->twig;
    }

    public function setTwig(string $twig): void
    {
        $this->twig = $twig;
    }

    public function getCss(): string
    {
        return $this->css;
    }

    public function setCss(string $css): void
    {
        $this->css = $css;
    }

    public function getJavascript(): string
    {
        return $this->javascript;
    }

    public function setJavascript(string $javascript): void
    {
        $this->javascript = $javascript;
    }

    public function getACLPermissions(): array
    {
        return ['view'];
    }

    public function getACLParameters(): ACLParameters
    {
        return new ACLParameters(
            Page::class,
            (string)$this->getId(),
            'forumify_admin_cms_page_edit',
            ['identifier' => $this->getId()]
        );
    }
}
