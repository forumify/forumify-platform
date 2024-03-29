<?php

declare(strict_types=1);

namespace Forumify\Cms\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SluggableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Cms\Repository\PageRepository;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;
    use SluggableEntityTrait;

    #[ORM\Column]
    private string $title = '';

    #[ORM\Column(unique: true)]
    private string $urlKey = '';

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
}
