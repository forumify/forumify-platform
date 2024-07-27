<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\PluginRepository;
use Forumify\ForumifyBundle;
use Forumify\Plugin\AbstractForumifyPlugin;
use Forumify\Plugin\Service\PluginService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    public function __construct(private readonly PluginRepository $pluginRepository)
    {
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

        $activePlugins = $this->pluginRepository->findByActive();

        $pluginPermissions = [];
        foreach ($activePlugins as $plugin) {
            $pluginClass = $plugin->getPluginClass();
            $pluginInstance = new $pluginClass();
            if ($pluginInstance instanceof AbstractForumifyPlugin && method_exists($pluginInstance, 'getPermissions')) {
                $pluginPermissions = array_merge_recursive($pluginPermissions, $pluginInstance::getPermissions());
            }
        }

        $allPermissions = array_merge_recursive($forumifyPermissions, $pluginPermissions);

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
}
