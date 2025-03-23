<?php

declare(strict_types=1);

namespace Forumify\Cms\EventSubscriber;

use Forumify\Admin\Crud\Event\PostSaveCrudEvent;
use Forumify\Admin\Crud\Event\PreSaveCrudEvent;
use Forumify\Cms\Entity\Page;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class PageEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreSaveCrudEvent::getName(Page::class) => 'preSavePage',
            PostSaveCrudEvent::getName(Page::class) => 'postSavePage',
        ];
    }

    /**
     * @param PreSaveCrudEvent<Page> $event
     */
    public function preSavePage(PreSaveCrudEvent $event): void
    {
        $page = $event->getEntity();

        if ($event->isNew() && empty($page->getTwig())) {
            $page->setTwig($page->getType() === 'twig'
                ? $this->defaultTwigTemplate()
                : '[]');
        }
    }

    /**
     * @param PostSaveCrudEvent<Page> $event
     */
    public function postSavePage(PostSaveCrudEvent $event): void
    {
        $page = $event->getEntity();

        $name = $page->getUrlKey();
        $cls = $this->twig->getTemplateClass($name);
        $key = $this->twig->getCache(false)->generateKey($name, $cls);

        $fs = new Filesystem();
        if ($fs->exists($key)) {
            $fs->remove($key);
        }
    }

    private function defaultTwigTemplate(): string
    {
        return <<<DOC
{% extends '@Forumify/frontend/page.html.twig' %}
{% block body %}
    <p>Learn more about customizing pages in the <a href="https://docs.forumify.net/user-manual/cms" target="_blank">CMS documentation.</a></p>
{% endblock %}
DOC;
    }
}
