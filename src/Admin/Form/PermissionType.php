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

class PermissionType extends AbstractType implements DataMapperInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'permissions' => [],
            'data' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['permissions'] as $group => $permission) {
            if (is_array($permission)) {
                $builder->add($group, self::class, [
                    'permissions' => $permission,
                    'label' => $group,
                    'data' => $this->getInitialData($group, $options['data'])
                ]);
            } else {
                $builder->add($permission, CheckboxType::class, [
                    'required' => false,
                    'label' => $permission,
                    'data' => in_array($permission, $options['data'])
                ]);
            }
        }
        $builder->setDataMapper($this);
    }

    private function getInitialData(string $group, array $data): array
    {

        $initialData = [];
        if (isset($data[$group])) {
            foreach ($data[$group] as $subGroup => $permissions) {
                if (is_array($permissions)) {
                    $initialData[$subGroup] = $this->getInitialData($subGroup, $data[$group]);
                } else {
                    $initialData[] = $permissions;
                }
            }
        }
        return $initialData;
    }

    public function mapDataToForms(mixed $data, Traversable $forms): void
    {
        foreach ($forms as $form) {
            if (!$form instanceof FormInterface) {
                continue;
            }

            $name = $form->getName();
            $isChecked = in_array($name, $data, true);

            if ($form->count() > 0) {
                $this->mapDataToForms($data[$name] ?? [], $form);
            } else {
                $form->setData($isChecked);
            }
        }
    }

    public function mapFormsToData(Traversable $forms, &$data): void
    {
        $permissions = [];
        foreach ($forms as $form) {
            if (!$form instanceof FormInterface) {
                continue;
            }

            $name = $form->getName();
            $isChecked = $form->getData();

            if ($form->count() > 0) {
                $subFormData = [];
                $this->mapFormsToData($form, $subFormData);
                if ($isChecked) {
                    $permissions[$name] = $subFormData;
                }
            } elseif ($isChecked) {
                $permissions[] = $name;
            }
        }
        $data = $this->flattenPermissions($permissions);
    }

    private function flattenPermissions(array $permissions, string $prefix = ''): array
    {
        $flat = [];

        foreach ($permissions as $key => $value) {
            $fieldName = $prefix . $key;

            if (is_array($value)) {
                $flat = array_merge($flat, $this->flattenPermissions($value, $fieldName . '.'));
            } else {
                $flat[$fieldName] = $prefix . $value;
            }
        }

        return $flat;
    }

    private function unflattenPermissions(array $permissions): array
    {
        $nested = [];

        foreach ($permissions as $value) {
            if (is_string($value)) {
                $keys = explode('.', $value);
                $permission = array_pop($keys);
                $array =& $nested;

                foreach ($keys as $part) {
                    if (!isset($array[$part])) {
                        $array[$part] = [];
                    }
                    $array =& $array[$part];
                }

                if (!in_array($permission, $array)) {
                    $array[] = $permission;
                }
            }
        }

        return $nested;
    }
}
