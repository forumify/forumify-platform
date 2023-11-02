<?php

declare(strict_types=1);

namespace Forumify\Core\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Forumify\Core\Entity\Setting;

class SettingFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->createSetting($manager, 'forum.title', 'forumify');
        $this->createSetting($manager, 'forum.logo', 'forumify-64e4f8aea0bdb.svg');

        $manager->flush();
    }

    private function createSetting(ObjectManager $manager, string $key, string $value):void
    {
        $setting = new Setting($key);
        $setting->setValue($value);
        $manager->persist($setting);
    }
}
