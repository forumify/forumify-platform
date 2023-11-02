<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use cebe\markdown\GithubMarkdown;
use DOMDocument;
use DOMNode;

class MarkdownParser
{
    public function parse(string $markdown): string
    {
        $parser = new GithubMarkdown();

        $html = $parser->parse($markdown);
        return $this->sanitizeHtml($html);
    }

    private function sanitizeHtml(string $html): string
    {
        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $this->stripScriptTags($dom);

        return $dom->saveHTML();
    }

    private function stripScriptTags(DOMDocument $dom): void
    {
        $scriptNodes = $dom->getElementsByTagName('script');

        /** @var DOMNode $tag */
        foreach ($scriptNodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }
}
