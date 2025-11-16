<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Doctrine\ORM\EntityRepository;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormInterface;

class GalleryWidget extends AbstractWidget
{
    public function __construct(private readonly ForumRepository $forumRepository)
    {
    }

    public function getName(): string
    {
        return 'forum.gallery';
    }

    public function getCategory(): string
    {
        return 'forum';
    }

    public function getPreview(): string
    {
        return '<div class="flex justify-between items-center h-100">
            <i
                class="ph ph-arrow-left p-2"
                style="color: var(--c-primary-text); background-color: var(--c-primary); border-radius: var(--border-radius)"
            ></i>
            <img
                width="100%"
                height="auto"
                style="max-width: 100%; max-height: 100px"
                draggable="false"
                src="/bundles/forumify/images/forumify.svg"
            >
            <i
                class="ph ph-arrow-right p-2"
                style="color: var(--c-primary-text); background-color: var(--c-primary); border-radius: var(--border-radius)"
            ></i>
        </div>';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/gallery.html.twig';
    }

    /**
     * @param array<string, mixed> $data
     * @return FormInterface<array<string, mixed>|null>
     */
    public function getSettingsForm(array $data = []): ?FormInterface
    {
        $data['forum'] = ($data['forum'] ?? false)
            ? $this->forumRepository->find($data['forum'])
            : null;

        $data['autoscroll'] = (bool)($data['autoscroll'] ?? false);
        $data['autoscrollInterval'] = (int)($data['autoscrollInterval'] ?? 10);
        $data['hideControls'] = (bool)($data['showControls'] ?? false);

        return $this
            ->createForm($data)
            ->add('forum', EntityType::class, [
                'placeholder' => 'admin.cms.widget.gallery.forum_placeholder',
                'help' => 'admin.cms.widget.gallery.forum_help',
                'autocomplete' => true,
                'class' => Forum::class,
                'choice_label' => 'title',
                'query_builder' => fn (EntityRepository $er) => $er
                    ->createQueryBuilder('f')
                    ->where('f.type IN (:imageType, :mixedType)')
                    ->setParameter('imageType', Forum::TYPE_IMAGE)
                    ->setParameter('mixedType', Forum::TYPE_MIXED),
            ])
            ->add('autoscroll', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.cms.widget.gallery.autoscroll_help',
            ])
            ->add('autoscrollInterval', NumberType::class, [
                'required' => false,
                'help' => 'admin.cms.widget.gallery.autoscroll_interval_help',
            ])
            ->add('hideControls', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.cms.widget.gallery.hide_controls_help',
            ])
            ->getForm()
        ;
    }
}
