<?php

declare(strict_types=1);

namespace Forumify\Cms\Form;

use Forumify\Cms\Entity\Resource;
use Forumify\Core\Form\UploadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class ResourceType extends AbstractType
{
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

        $builder->add('name', TextType::class, [
            'disabled' => $resource !== null,
        ]);

        if ($resource !== null) {
            $builder->add('slug', TextType::class, [
                'disabled' => true,
            ]);
        }

        $builder->add('path', UploadType::class, [
            'label' => 'File',
            'filesystem' => 'resource.storage',
            'asset_package' => 'forumify.resource',
        ]);
    }
}
