<?php

declare(strict_types=1);

namespace Forumify\Page\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SluggableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Page\Repository\PageRepository;


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
    private string $source = '';

    #[ORM\Column]
    private string $type = 'html';

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

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
