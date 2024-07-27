<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

class PermissionType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'permissions' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['permissions'] as $group => $permission) {
            if (is_array($permission)) {
                $builder->add($group, self::class, [
                    'permissions' => $permission,
                    'label' => $group,
                ]);
                continue;
            }

            $builder->add($permission, CheckboxType::class, [
                'required' => false,
                'label' => $permission,
            ]);
        }

        $builder->setDataMapper($this);
    }

    /**
     * @inheritDoc
     */
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        $data = [];
        foreach ($viewData as $value) {
            $split = explode('.', $value, 2);

            if (count($split) > 1) {
                $data[$split[0]][] = $split[1];
                continue;
            }
            $data[$split[0]] = true;
        }

        foreach ($forms as $form) {
            $formData = $data[$form->getName()] ?? null;
            $form->setData($formData);
        }
    }

    /**
     * @inheritDoc
     */
    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        $viewData = [];

        foreach ($forms as $form) {
            $key = $form->getName();
            $data = $form->getData();

            if (!is_array($data)) {
                if ($data) {
                    $viewData[] = $key;
                }
                continue;
            }

            foreach ($data as $item) {
                $viewData[] = $key . '.' . $item;
            }
        }
    }
}
