<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use DateTime;
use Dom\HTMLDocument;
use Forumify\Core\Entity\User;
use Forumify\Core\Service\HTMLSanitizer;
use Twig\Environment;

class QuoteService
{
    private const NESTED_QUOTE_SELECTOR = 'blockquote.forumify-quote';

    public function __construct(
        private readonly Environment $twig,
        private readonly HTMLSanitizer $sanitizer,
    ) {
    }

    public function createQuote(string $content, ?User $author, DateTime $date, ?string $source = null): string
    {
        return $this->twig->render('@Forumify/frontend/components/quote.html.twig', [
            'content' => $this->removeNestedQuotes($this->sanitizer->sanitize($content)),
            'author' => $author,
            'date' => $date,
            'source' => $source,
        ]);
    }

    private function removeNestedQuotes(string $content): string
    {
        $document = HTMLDocument::createFromString($content, LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR);

        $quotes = iterator_to_array($document->querySelectorAll(self::NESTED_QUOTE_SELECTOR));
        foreach ($quotes as $quote) {
            $quote->remove();
        }

        return trim($document->saveHtml());
    }
}
