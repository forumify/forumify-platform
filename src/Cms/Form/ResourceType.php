<?php

declare(strict_types=1);

namespace Forumify\Cms\Form;

use Forumify\Cms\Entity\Resource;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceType extends AbstractType
{
    public function __construct(
        private readonly Packages $packages,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Resource::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Resource|null $resource */
        $resource = $options['data'] ?? null;

        $builder
            ->add('name', TextType::class)
            ->add('file', FileType::class, [
                'mapped' => false,
                'required' => $resource === null,
                'attr' => [
                    'preview' => !empty($resource?->getPath())
                        ? $this->packages->getUrl($resource->getPath(), 'forumify.resource')
                        : null,
                ],
            ]);
    }
}
