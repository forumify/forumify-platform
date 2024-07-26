<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Entity\Role;
use Forumify\ForumifyBundle;
use Forumify\Plugin\Service\PluginService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    private PluginService $pluginService;

    public function __construct(PluginService $pluginService)
    {
        $this->pluginService = $pluginService;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
            'permissions' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $forumifyPermissions = ForumifyBundle::getPermissions();
        $installedPlugins = $this->pluginService->getInstalledPlugins();

        $pluginPermissions = [];
        foreach ($installedPlugins as $pluginPackage => $pluginInfo) {
            $pluginClass = $pluginInfo['extra']['forumify-plugin-class'] ?? null;
            if ($pluginClass !== null && class_exists($pluginClass)) {
                $pluginInstance = new $pluginClass();
                if (method_exists($pluginInstance, 'getPermissions')) {
                    $pluginPermissions = $this->recursiveMerge($pluginPermissions, $pluginInstance::getPermissions());
                }
            }
        }

        $allPermissions = $this->recursiveMerge($forumifyPermissions, $pluginPermissions);

        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('administrator', CheckboxType::class, [
                'required' => false,
                'help' => 'role_type.administrator'
            ])
            ->add('moderator', CheckboxType::class, [
                'required' => false,
                'help' => 'role_type.moderator'
            ])
            ->add('permissions', PermissionType::class, [
                'permissions' => $allPermissions,
                'label' => 'Permissions',
                'data' => $options['data']->getPermissions(),
            ]);
    }

    private function recursiveMerge(array $array1, array $array2): array
    {
        $merged = $array1;
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->recursiveMerge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }
}
