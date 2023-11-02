<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Symfony\Component\Validator\Constraints as Assert;

class NewTopic
{
    #[Assert\Length(max: 255, normalizer: 'trim')]
    private string $title;

    #[Assert\NotBlank]
    private string $content;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
