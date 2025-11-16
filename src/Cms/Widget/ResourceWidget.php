<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Forumify\Cms\Entity\Resource;
use Forumify\Cms\Repository\ResourceRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResourceWidget extends AbstractWidget
{
    public function __construct(
        private readonly ResourceRepository $resourceRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getName(): string
    {
        return 'content.resource';
    }

    public function getCategory(): string
    {
        return 'content';
    }

    public function getPreview(): string
    {
        return '<img
            width="100%"
            height="auto"
            style="max-width: 100%; max-height: 100%"
            draggable="false"
            src="/bundles/forumify/images/forumify.svg"
            data-setting-resource="src"
        >';
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/resource.html.twig';
    }

    /**
     * @param array<string, mixed> $data
     * @return FormInterface<array<string, mixed>|null>
     */
    public function getSettingsForm(array $data = []): ?FormInterface
    {
        $resources = [];
        /** @var Resource $resource */
        foreach ($this->resourceRepository->findAll() as $resource) {
            $url = $this->urlGenerator->generate('forumify_admin_cms_page_builder_resource', [
                'slug' => $resource->getSlug()
            ]);

            $resources[$resource->getName()] = $url;
        }

        return $this->createForm($data)
            ->add('resource', ChoiceType::class, [
                'autocomplete' => true,
                'choices' => $resources,
                'placeholder' => 'Select a resource',
            ])
            ->getForm()
        ;
    }
}
