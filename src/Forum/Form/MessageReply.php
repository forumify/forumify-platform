<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Symfony\Component\Serializer\Attribute\Groups;

class MessageReply
{
    #[Groups('Message::write')]
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
