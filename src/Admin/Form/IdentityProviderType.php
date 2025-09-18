<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\OAuth\Entity\IdentityProvider;
use Forumify\OAuth\Idp\IdentityProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IdentityProviderType extends AbstractType
{
    /**
     * @param iterable<IdentityProviderInterface>
     */
    public function __construct(
        #[AutowireIterator('forumify.oauth.identity_provider', defaultIndexMethod: 'getType')]
        private readonly iterable $idps
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IdentityProvider::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('type', ChoiceType::class, [
                'placeholder' => 'admin.identity_provider.type_placeholder',
                'choices' => [
                    'Discord' => 'discord',
                ],
            ])
        ;

        if (empty($options['data'])) {
            return;
        }

        $idps = iterator_to_array($this->idps);
        $type = $options['data']->getType();
        /** @var IdentityProviderInterface|null $idp */
        $idp = $idps[$type] ?? null;
        if ($idp === null) {
            return;
        }

        $builder->add('data', $idp::getDataType());
    }
}
