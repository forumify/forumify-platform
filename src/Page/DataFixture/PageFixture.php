<?php

declare(strict_types=1);

namespace Forumify\Page\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Forumify\Core\DataFixture\UserFixture;
use Forumify\Core\Entity\User;
use Forumify\Page\Entity\Page;

class PageFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var User $admin */
        $admin = $this->getReference(UserFixture::ADMIN_REFERENCE);

        $page = new Page();
        $page->setTitle('index');
        $page->setUrlKey('');
        $page->setCreatedBy($admin);
        $page->setSource(<<<'SOURCE'
{% extends '@Forumify/frontend/base.html.twig' %}
{% block body %}
    <p style="text-align: center">This is a temporary homepage, you can customize it in the admin panel.</p>
{% endblock %}
SOURCE
        );
        $manager->persist($page);

        $page = new Page();
        $page->setTitle('about');
        $page->setUrlKey('about');
        $page->setCreatedBy($admin);
        $page->setSource(<<<'SOURCE'
{% extends '@Forumify/frontend/base.html.twig' %}
{% block body %}
    My about page
{% endblock %}
SOURCE
        );
        $manager->persist($page);

        $page = new Page();
        $page->setTitle('mission');
        $page->setUrlKey('mission');
        $page->setCreatedBy($admin);
        $page->setSource(<<<'SOURCE'
{% extends '@Forumify/frontend/base.html.twig' %}
{% block body %}
    My mission statement
{% endblock %}
SOURCE
        );
        $manager->persist($page);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
