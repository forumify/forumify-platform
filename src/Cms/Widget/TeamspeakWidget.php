<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;

class TeamspeakWidget extends AbstractWidget
{
    public function __construct(
        private readonly Packages $packages,
    ) {
    }

    public function getName(): string
    {
        return 'social.teamspeak';
    }

    public function getCategory(): string
    {
        return 'social';
    }

    public function getPreview(): string
    {
        $tssvg = $this->packages->getUrl('bundles/forumify/images/teamspeak.svg');
        return "<button type='button' class='btn-primary' style='
            color: #1C80BE;
            background-color: white;
            border: 1px solid  rgba(255, 255, 255, 0.08);
        '>
            <img src='$tssvg' width='20' height='20' class='mr-2'>
            <span data-setting-serverName='innerText'></span>
        </button>";
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/teamspeak.html.twig';
    }

    public function getSettingsForm(array $data = []): ?FormInterface
    {
        return $this->createForm($data)
            ->add('tsviewerId', TextType::class, [
                'help' => 'admin.cms.widget.social.teamspeak_tsviewer_help',
                'help_html' => true,
            ])
            ->add('serverName', TextType::class, [
                'help' => 'admin.cms.widget.social.teamspeak_server_name_help',
            ])
            ->getForm()
        ;
    }
}
