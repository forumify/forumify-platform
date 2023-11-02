<?php

declare(strict_types=1);

namespace Forumify\Forum\DataFixture;

use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Forumify\Core\DataFixture\UserFixture;
use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\Entity\Topic;

class ForumFixture extends Fixture implements DependentFixtureInterface
{
    private DateTime $date;

    public function __construct() {
        $this->date = new DateTime('2000-01-01 00:00:00');
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $admin */
        $admin = $this->getReference(UserFixture::ADMIN_REFERENCE);

        $group = new ForumGroup();
        $group->setTitle('General');
        $manager->persist($group);

        $general = new Forum();
        $general->setTitle('General');
        $general->setContent('Any discussion is allowed here!');
        $general->setGroup($group);
        $manager->persist($general);

        foreach ([
            'Recent movies or TV shows',
            'Hobbies and interests',
            'Book recommendations',
            'Fitness and healthy living tips',
            'Food and cooking experiences',
            'Technology and gadgets',
            'Current events and news',
            'Music and favorite artists',
            'Fashion trends and personal style',
            'Personal goals and aspirations',
            'Funny or interesting anecdotes',
            'Environmental issues and sustainability',
            'Relationship advice and experiences',
            'Art and creativity',
            'Sports and favorite teams',
            'Memorable vacations or trips',
            'Cultural traditions and celebrations',
            'Home improvement and decor ideas',
            'Personal growth and self-improvement techniques',
        ] as $topic) {
            $manager->persist($this->topic($general, $topic, 'Initial comment'));
        }

        $topic = $this->topic($general, 'Favorite travel destinations', 'Name your favorite travel destinations or share your plans to travel!');
        $manager->persist($topic);

        foreach ([
            "Hey, guys! I've been doing some research, and I think we should consider going on vacation to Albania. It looks absolutely stunning!",
            "Albania? Really? I haven't heard much about it as a vacation destination. What's so special about it?",
            "Well, first of all, Albania has some of the most breathtaking beaches in Europe. The Albanian Riviera is known for its pristine waters and untouched beauty. I've seen pictures of Ksamil and Saranda, and they look like paradise!",
            "That sounds amazing! I'm always up for a beach vacation. But what else does Albania have to offer?",
            "Albania has a rich history and diverse culture. We can explore the ancient ruins of Butrint, a UNESCO World Heritage site. And in the capital city of Tirana, we can visit the vibrant Blloku neighborhood and the historic Skanderbeg Square.",
            "That sounds intriguing! I'm a foodie, so what's the culinary scene like in Albania?",
            "Albanian cuisine is delicious! We can try traditional dishes like byrek, a savory pastry filled with cheese, meat, or vegetables. And don't miss out on trying some of their fresh seafood and baklava for dessert.",
            "Okay, you've piqued my interest. How easy is it to get around in Albania? Do they have a good transportation system?",
            "Albania has a well-connected transportation system. We can rent a car and drive along the scenic coastal roads or use public transportation like buses and trains to get around. Plus, it's relatively affordable compared to other European countries.",
            "I've heard that Albania is quite affordable overall. Is that true?",
            "Absolutely! Albania is known for being a budget-friendly destination. Accommodation, food, and transportation are generally more affordable compared to other European countries. We can enjoy a fantastic vacation without breaking the bank.",
            "It sounds like Albania has a lot to offer. I'm definitely open to the idea. What do you all think?",
            "I'm on board. The combination of stunning beaches, rich history, and delicious food is hard to resist.",
            "I'm excited to explore a lesser-known destination. Let's do it!",
            "Great! I'll start looking into flights and accommodations. Albania, here we come!",
            "Hey, everyone! I've been thinking about our next vacation, and I believe Poland would be a fantastic choice.",
            "Poland? That's an interesting suggestion. What makes it a great vacation destination?",
            "Well, Poland offers a diverse range of attractions. We can explore the historic city of Krakow, visit the stunning Wawel Castle, and take a tour of the famous Wieliczka Salt Mine.",
            "That sounds intriguing! What else can we do in Poland?",
            "Poland is also known for its breathtaking natural beauty. We can visit the Tatra Mountains and go hiking in Zakopane. And let's not forget about the beautiful Masurian Lakes region.",
            "I've heard that Polish cuisine is delicious. What traditional dishes should we try?",
            "Polish cuisine is indeed delightful! We must try pierogi, traditional dumplings filled with various ingredients. And don't miss out on tasting some authentic Polish sausages and the famous Polish vodka.",
            "How about transportation in Poland? Is it easy to get around?",
            "Poland has a well-developed transportation system. We can use trains or buses to travel between cities. In major cities like Warsaw and Krakow, there are also efficient public transportation networks.",
            "Is Poland an affordable destination for a vacation?",
            "Yes, Poland is generally considered an affordable destination. The cost of accommodation, food, and attractions is reasonable compared to other European countries, making it budget-friendly for travelers.",
            "I'm starting to get excited about the idea of visiting Poland. What do you all think?",
            "I'm intrigued. The mix of history, nature, and delicious food sounds like a great vacation combination.",
            "I agree. Poland has so much to offer, and I'm particularly interested in exploring its rich history and trying the local cuisine.",
            "Alright, it's settled then. Let's start planning our vacation to Poland!",
            "Absolutely! I'll begin researching flights, accommodations, and the best places to visit. Poland, here we come!",
            "Hey, everyone! I've been thinking about our next vacation, and I think Amsterdam would be an incredible choice.",
            "Amsterdam? That sounds interesting. What makes it a great vacation destination?",
            "Well, Amsterdam is known for its charming canals, historic architecture, and vibrant culture. We can take a boat tour along the canals, visit iconic landmarks like the Anne Frank House and the Van Gogh Museum.",
            "That sounds amazing! What else can we do in Amsterdam?",
            "There's so much to do! We can explore the lively neighborhoods like Jordaan and De Pijp, rent bicycles and ride through Vondelpark, and visit the famous flower market, Bloemenmarkt.",
            "I've heard about the nightlife in Amsterdam. Is it as vibrant as they say?",
            "Absolutely! Amsterdam is renowned for its nightlife. We can experience the lively atmosphere of the Red Light District, enjoy live music in the famous jazz clubs, and visit trendy bars and clubs.",
            "How about the food in Amsterdam? Are there any traditional dishes we should try?",
            "Amsterdam offers a variety of culinary delights. We must try the famous Dutch pancakes, bitterballen (a savory snack), and raw herring from a herring stand. And of course, we can't forget to indulge in some delicious Dutch cheese.",
            "Is it easy to get around Amsterdam?",
            "Definitely! Amsterdam has an excellent public transportation system. We can use trams, buses, or even rent bicycles to navigate the city easily. It's also a walkable city, so exploring on foot is a great option.",
            "Is Amsterdam an expensive city to visit?",
            "Amsterdam can be relatively expensive compared to some other European cities, but there are ways to make it more budget-friendly. We can find affordable accommodations, try street food or local markets for meals, and take advantage of free or discounted attractions.",
            "I'm getting excited about the idea of visiting Amsterdam. What do you all think?",
            "I'm on board! The combination of history, art, and the unique Amsterdam atmosphere sounds like a perfect vacation.",
            "I'm intrigued by the cultural experiences Amsterdam offers. I'd love to explore its museums and immerse myself in the local art scene.",
            "Alright, let's start planning our vacation to Amsterdam!",
            "Absolutely! I'll research flights, accommodations, and the must-see attractions. Amsterdam, here we come!"
        ] as $comment) {
            $manager->persist($this->comment($topic, $comment));
        }

        $gallery = new Forum();
        $gallery->setTitle('Gallery');
        $gallery->setContent('Post your epic cat pictures or memes.');
        $gallery->setGroup($group);
        $manager->persist($gallery);

        $group = new ForumGroup();
        $group->setTitle('Programming');
        $manager->persist($group);

        $php = new Forum();
        $php->setTitle('PHP');
        $php->setContent('General PHP discussion.');
        $php->setGroup($group);
        $manager->persist($php);

        $symfony = new Forum();
        $symfony->setTitle('Symfony');
        $symfony->setContent('Discussions about <a href="https://symfony.com/">Symfony</a>.');
        $symfony->setParent($php);
        $manager->persist($symfony);

        $topic = $this->topic($symfony, 'Forumify is written in Symfony!', 'Did you know that Forumify is written in <strong>Symfony</strong>?<br/>I think that\'s very cool!');
        $manager->persist($topic);

        $comment = $this->comment($topic, 'Oh woah! I think I\'m going to create some bundles for it and publish them on the marketplace!');
        $manager->persist($comment);

        $symfonyBundles = new Forum();
        $symfonyBundles->setTitle('Symfony Bundles');
        $symfonyBundles->setContent('Discussions about <a href="https://symfony.com/">Symfony</a> Bundles.');
        $symfonyBundles->setParent($symfony);
        $manager->persist($symfonyBundles);

        $topic = new Topic();
        $topic->setForum($symfonyBundles);
        $topic->setTitle('Forumify Bundle');
        $topic->setCreatedBy($admin);
        $manager->persist($topic);

        $comment = new Comment();
        $comment->setContent('Discover Forumify, the latest forum platform!');
        $comment->setCreatedBy($admin);
        $comment->setTopic($topic);
        $manager->persist($comment);

        $laravel = new Forum();
        $laravel->setTitle('Laravel');
        $laravel->setContent('Discussions about <a href="https://laravel.com/">Laravel</a>.');
        $laravel->setParent($php);
        $manager->persist($laravel);

        $topic = new Topic();
        $topic->setForum($laravel);
        $topic->setTitle('Laravel uses a lot of Symfony components!');
        $topic->setCreatedBy($admin);
        $manager->persist($topic);

        $comment = new Comment();
        $comment->setContent('Did you know that Laravel uses a lot of Symfony components?');
        $comment->setCreatedBy($admin);
        $comment->setTopic($topic);
        $manager->persist($comment);

        $javascript = new Forum();
        $javascript->setTitle('JavaScript');
        $javascript->setContent('General JavaScript discussion.');
        $javascript->setGroup($group);
        $manager->persist($javascript);

        $typescript = new Forum();
        $typescript->setTitle('TypeScript');
        $typescript->setContent('Discussions about TypeScript.');
        $typescript->setParent($javascript);
        $manager->persist($typescript);

        $react = new Forum();
        $react->setTitle('React');
        $react->setContent('Discussions about React.');
        $react->setParent($javascript);
        $manager->persist($react);

        $angular = new Forum();
        $angular->setTitle('Angular');
        $angular->setContent('Discussions about Angular.');
        $angular->setParent($javascript);
        $manager->persist($angular);

        $manager->flush();
    }

    private function topic(Forum $forum, string $title, string $commentText = ''): Topic
    {
        /** @var User $admin */
        $admin = $this->getReference(UserFixture::ADMIN_REFERENCE, User::class);

        $topic = new Topic();
        $topic->setTitle($title);
        $topic->setForum($forum);
        $topic->setCreatedAt($this->getCreatedAt());
        $topic->setCreatedBy($admin);

        if (!empty($commentText)) {
            $comment = $this->comment($topic, $commentText);
            $topic->addComment($comment);
        }

        return $topic;
    }

    private function comment(Topic $topic, string $text): Comment
    {
        /** @var User $admin */
        $admin = $this->getReference(UserFixture::ADMIN_REFERENCE, User::class);

        $comment = new Comment();
        $comment->setContent($text);
        $comment->setCreatedBy($admin);
        $comment->setCreatedAt($this->getCreatedAt());
        $comment->setTopic($topic);

        return $comment;
    }

    private function getCreatedAt(): DateTime
    {
        $this->date->add(new DateInterval('PT1H'));
        return clone $this->date;
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
