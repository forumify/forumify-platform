<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;

class DiscordWidget extends AbstractWidget
{
    public function getName(): string
    {
        return 'social.discord';
    }

    public function getCategory(): string
    {
        return 'social';
    }

    public function getPreview(): string
    {
        return '<button type="button" class="btn-primary" style="
            color: white;
            background-color: #5865f2;
            border: 1px solid  rgba(255, 255, 255, 0.08);
        ">
            <i class="ph-fill ph-discord-logo"></i>
            <span data-setting-serverName="innerText"></span>
        </button>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/discord.html.twig';
    }

    public function getSettingsForm(array $data = []): ?FormInterface
    {
        return $this->createForm($data)
            ->add('serverId', TextType::class, [
                'help' => 'admin.cms.widget.social.discord_server_id_help',
                'help_html' => true,
            ])
            ->add('serverName', TextType::class, [
                'help' => 'admin.cms.widget.social.discord_server_name_help',
            ])
            ->getForm()
        ;
    }
}
