<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Symfony\Component\Validator\Constraints as Assert;

class NewComment
{
    #[Assert\NotBlank(normalizer: 'trim')]
    private string $content;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
