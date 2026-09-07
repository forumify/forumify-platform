<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Cms\Entity\Page;
use Forumify\Cms\Repository\PageRepository;
use Forumify\Core\Compliance\ComplianceMode;
use Forumify\Core\Form\InfoType;
use Forumify\Core\Form\UploadType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class ConfigurationType extends AbstractType
{
    public function __construct(
        #[Autowire(env: 'bool:FORUMIFY_HOSTED_INSTANCE')]
        private readonly bool $isHostedInstance,
        private readonly PageRepository $pageRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('forumify__title', TextType::class, [
                'label' => 'admin.configuration.forum_title',
            ])
            ->add('forumify__logo', UploadType::class, [
                'label' => 'admin.configuration.logo',
                'required' => false,
                'filesystem' => 'asset.storage',
                'asset_package' => 'forumify.asset',
                'accept' => 'image/*',
                'file_constraints' => [new Assert\Image(maxSize: '10M')],
            ])
            ->add('forumify__default_avatar', UploadType::class, [
                'label' => 'admin.configuration.default_avatar',
                'help' => 'admin.configuration.default_avatar_help',
                'required' => false,
                'filesystem' => 'avatar.storage',
                'asset_package' => 'forumify.avatar',
                'accept' => 'image/*',
                'file_constraints' => [new Assert\Image(maxSize: '10M')],
            ])
            ->add('forumify__enable_registrations', CheckboxType::class, [
                'label' => 'admin.configuration.enable_registrations',
                'required' => false,
            ])
            ->add('forumify__hide_usernames', CheckboxType::class, [
                'label' => 'admin.configuration.hide_usernames',
                'required' => false,
            ])
            ->add('forumify__login_method', ChoiceType::class, [
                'label' => 'admin.configuration.login_method',
                'help' => 'admin.configuration.login_method_help',
                'required' => false,
                'choices' => [
                    'Username' => 'username',
                    'Email' => 'email',
                    'Both' => 'both',
                ],
                'placeholder' => null,
            ])
            ->add('forumify__index', TextType::class, [
                'label' => 'admin.configuration.index',
                'help' => 'admin.configuration.index_help',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('forumify__default_timezone', TimezoneType::class, [
                'label' => 'admin.configuration.default_timezone',
                'help' => 'admin.configuration.default_timezone_help',
                'required' => false,
                'autocomplete' => true,
                'placeholder' => 'UTC',
            ])
            ->add('forumify__enable_auto_updates', CheckboxType::class, [
                'label' => 'admin.configuration.enable_auto_updates',
                'help' => 'admin.configuration.enable_auto_updates_help',
                'required' => false,
            ])
            ->add('forumify__readonly', CheckboxType::class, [
                'label' => 'admin.configuration.readonly',
                'help' => 'admin.configuration.readonly_help',
                'required' => false,
            ])
            ->add('forumify__recaptcha__info', InfoType::class, [
                'label' => 'admin.configuration.recaptcha_info',
                'help' => 'admin.configuration.recaptcha_help',
            ])
            ->add('forumify__recaptcha__enabled', CheckboxType::class, [
                'label' => 'admin.configuration.recaptcha_enabled',
                'required' => false,
            ])
            ->add('forumify__recaptcha__site_key', TextType::class, [
                'label' => 'admin.configuration.recaptcha_site_key',
                'required' => false,
            ])
            ->add('forumify__recaptcha__site_secret', TextType::class, [
                'label' => 'admin.configuration.recaptcha_site_secret',
                'required' => false,
            ])
            ->add('forumify__recaptcha__min_score', NumberType::class, [
                'label' => 'admin.configuration.recaptcha_min_score',
                'help' => 'admin.configuration.recaptcha_min_score_help',
                'required' => false,
                'constraints' => [
                    new Assert\Range(max: 1.0, min: 0.0),
                ],
            ])
            ->add('forumify__cf_turnstile__info', InfoType::class, [
                'label' => 'admin.configuration.turnstile.info',
                'help' => 'admin.configuration.turnstile.help',
            ])
            ->add('forumify__cf_turnstile__enabled', CheckboxType::class, [
                'label' => 'admin.configuration.turnstile.enabled',
                'required' => false,
            ])
            ->add('forumify__cf_turnstile__site_key', TextType::class, [
                'label' => 'admin.configuration.turnstile.site_key',
                'required' => false,
            ])
            ->add('forumify__cf_turnstile__site_secret', TextType::class, [
                'label' => 'admin.configuration.turnstile.site_secret',
                'required' => false,
            ])
        ;

        $this->addComplianceFields($builder);

        if (!$this->isHostedInstance) {
            $builder
                ->add('forumify__mailer__from', TextType::class, [
                    'label' => 'admin.configuration.mailer_from',
                    'help' => 'admin.configuration.mailer_from_help',
                    'required' => false,
                ]);
        }
    }

    /**
     * @param FormBuilderInterface<array<string, mixed>|null> $builder
     */
    private function addComplianceFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('forumify__compliance__mode', ChoiceType::class, [
                'label' => 'admin.configuration.compliance.mode',
                'help' => 'admin.configuration.compliance.mode_help',
                'required' => false,
                'placeholder' => null,
                'empty_data' => ComplianceMode::Off->value,
                'choices' => [
                    'admin.configuration.compliance.mode_off' => ComplianceMode::Off->value,
                    'admin.configuration.compliance.mode_generated' => ComplianceMode::Generated->value,
                    'admin.configuration.compliance.mode_cms_page' => ComplianceMode::CmsPage->value,
                ],
                'attr' => [
                    'data-forumify--compliance-target' => 'mode',
                    'data-action' => 'change->forumify--compliance#update',
                ],
            ])
            ->add('forumify__compliance__cms_page', ChoiceType::class, [
                'label' => 'admin.configuration.compliance.cms_page',
                'help' => 'admin.configuration.compliance.cms_page_help',
                'required' => false,
                'placeholder' => 'admin.configuration.compliance.cms_page_placeholder',
                'choices' => $this->getPageChoices(),
            ])
            ->add('forumify__compliance__controller_name', TextType::class, [
                'label' => 'admin.configuration.compliance.controller_name',
                'help' => 'admin.configuration.compliance.controller_name_help',
                'required' => false,
            ])
            ->add('forumify__compliance__controller_address', TextareaType::class, [
                'label' => 'admin.configuration.compliance.controller_address',
                'required' => false,
            ])
            ->add('forumify__compliance__contact_email', EmailType::class, [
                'label' => 'admin.configuration.compliance.contact_email',
                'help' => 'admin.configuration.compliance.contact_email_help',
                'required' => false,
            ])
            ->add('forumify__compliance__dpo_contact', TextType::class, [
                'label' => 'admin.configuration.compliance.dpo_contact',
                'help' => 'admin.configuration.compliance.dpo_contact_help',
                'required' => false,
            ])
            ->add('forumify__compliance__eu_representative', TextType::class, [
                'label' => 'admin.configuration.compliance.eu_representative',
                'help' => 'admin.configuration.compliance.eu_representative_help',
                'required' => false,
            ])
            ->add('forumify__compliance__supervisory_authority', TextType::class, [
                'label' => 'admin.configuration.compliance.supervisory_authority',
                'help' => 'admin.configuration.compliance.supervisory_authority_help',
                'required' => false,
            ])
            ->add('forumify__compliance__minimum_age', NumberType::class, [
                'label' => 'admin.configuration.compliance.minimum_age',
                'help' => 'admin.configuration.compliance.minimum_age_help',
                'required' => false,
                'html5' => true,
                'constraints' => [new Assert\Range(max: 21, min: 0)],
            ]);

        if ($this->isHostedInstance) {
            $builder->add('forumify__compliance__hosting_info', InfoType::class, [
                'label' => 'admin.configuration.compliance.hosting_info',
                'help' => 'admin.configuration.compliance.hosting_info_help',
            ]);
            return;
        }

        $builder
            ->add('forumify__compliance__hosting_provider', TextType::class, [
                'label' => 'admin.configuration.compliance.hosting_provider',
                'help' => 'admin.configuration.compliance.hosting_provider_help',
                'required' => false,
            ]);
    }

    /**
     * @return array<string, int>
     */
    private function getPageChoices(): array
    {
        $choices = [];
        /** @var Page $page */
        foreach ($this->pageRepository->findAll() as $page) {
            $choices[$page->getTitle()] = $page->getId();
        }

        return $choices;
    }
}
