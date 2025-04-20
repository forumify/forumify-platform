<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;

class YoutubeWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'content.youtube';
    }

    public function getCategory(): string
    {
        return 'content';
    }

    public function getPreview(): string
    {
        return '<div class="youtube-widget">
            <span class="text-huge text-bold text-center mt-4">VIDEO PREVIEW</span>
            <div class="play-button"></div>
        </div>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/youtube.html.twig';
    }

    public function getSettingsForm(array $data = []): ?FormInterface
    {
        return $this
            ->createForm($data)
            ->add('videoId', TextType::class, [
                'help' => 'admin.cms.widget.youtube.video_id_help',
                'help_html' => true,
            ])
            ->getForm()
        ;
    }
}
