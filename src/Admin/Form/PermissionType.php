<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

/**
 * @extends AbstractType<array<string, mixed>>
 */
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
     * @param list<string>|null $viewData
     * @param Traversable<FormInterface<mixed>> $forms
     * @return void
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
                $key = $split[0];
                if (!isset($data[$key]) || !is_array($data[$key])) {
                    $data[$key] = [];
                }
                $data[$key][] = $split[1];
            } else {
                $data[$split[0]] = true;
            }
        }

        foreach ($forms as $form) {
            $formData = $data[$form->getName()] ?? null;
            $form->setData($formData);
        }
    }

    /**
     * @inheritDoc
     * @param Traversable<FormInterface<mixed>> $forms
     * @param-out list<string> $viewData
     * @return void
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
