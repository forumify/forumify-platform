<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Forumify\Core\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class NewComment
{
    #[Assert\NotBlank(normalizer: 'trim')]
    private string $content;

    private ?User $author = null;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }
}
