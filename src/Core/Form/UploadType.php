<?php

declare(strict_types=1);

namespace Forumify\Core\Form;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadType extends AbstractType
{
    public function __construct(
        private readonly Packages $packages,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'asset_package',
            'filesystem',
        ]);

        $resolver->setDefaults([
            'data_class' => null,
            'multiple' => false,
        ]);

        $resolver->setAllowedTypes('asset_package', 'string');
        $resolver->setAllowedTypes('filesystem', 'string');
        $resolver->setAllowedTypes('multiple', 'bool');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $value = $form->getData();
        if (empty($value)) {
            $value = [];
        } elseif (!is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $v) {
            $view->vars['files'][] = [
                'name' => $v,
                'preview' => $this->packages->getUrl($v, $options['asset_package']),
            ];
        }
    }

    public function getParent(): string
    {
        return FileType::class;
    }
}
