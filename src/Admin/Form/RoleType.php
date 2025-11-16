<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\PluginRepository;
use Forumify\ForumifyBundle;
use Forumify\Plugin\AbstractForumifyPlugin;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class RoleType extends AbstractType
{
    public function __construct(private readonly PluginRepository $pluginRepository)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('administrator', CheckboxType::class, [
                'required' => false,
                'help' => 'role_type.administrator',
            ])
            ->add('permissions', PermissionType::class, [
                'permissions' => $this->getAvailablePermissions(),
                'label' => 'Permissions',
            ])
            ->add('showOnForum', CheckboxType::class, [
                'required' => false,
                'help' => 'role_type.show_on_forum',
            ])
            ->add('showOnUsername', CheckboxType::class, [
                'required' => false,
                'help' => 'role_type.show_on_username',
            ])
            ->add('color', ColorType::class, [
                'required' =>false,
                'help' => 'role_type.color',
            ])
        ;
    }

    /**
     * @return array<string, array<string, array<mixed>|string>>
     */
    private function getAvailablePermissions(): array
    {
        $slugger = new AsciiSlugger();

        $permissions = ['forumify' => ForumifyBundle::getPermissions()];
        $plugins = $this->pluginRepository->findBy(['active' => true, 'type' => Plugin::TYPE_PLUGIN]);
        foreach ($plugins as $plugin) {
            $pluginObj = $plugin->getPlugin();
            if (!$pluginObj instanceof AbstractForumifyPlugin) {
                continue;
            }

            $pluginPermissions = $pluginObj->getPermissions();
            if (empty($pluginPermissions)) {
                continue;
            }

            $pluginName = $pluginObj->getPluginMetadata()->name;
            $key = $slugger->slug($pluginName)->lower()->toString();
            $permissions[$key] = $pluginPermissions;
        }
        return $permissions;
    }
}
