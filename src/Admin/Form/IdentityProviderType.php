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

use function Symfony\Component\String\u;

/**
 * @extends AbstractType<IdentityProvider|null>
 */
class IdentityProviderType extends AbstractType
{
    /**
     * @param iterable<IdentityProviderInterface> $idpTypes
     */
    public function __construct(
        #[AutowireIterator('forumify.oauth.identity_provider', defaultIndexMethod: 'getType')]
        private readonly iterable $idpTypes
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
        /** @var IdentityProvider|null $idp */
        $idp = $options['data'] ?? null;

        /** @var array<string, IdentityProviderInterface> $idpTypes */
        $idpTypes = iterator_to_array($this->idpTypes);
        $idpChoices = [];
        $selectedType = null;
        foreach ($idpTypes as $key => $type) {
            $idpChoices[u($key)->title(true)->toString()] = $key;

            if ($idp !== null && $idp->getType() === $key) {
                $selectedType = $type;
            }
        }

        $builder
            ->add('name', TextType::class)
            ->add('type', ChoiceType::class, [
                'placeholder' => 'admin.identity_provider.type_placeholder',
                'choices' => $idpChoices,
                'disabled' => $idp !== null,
                'help' => $idp !== null ? 'admin.identity_provider.type_help' : null,
            ])
        ;

        if ($idp === null || $selectedType === null) {
            return;
        }

        $builder->add('data', $selectedType::getDataType(), [
            'label' => false,
            'idp' => $idp,
        ]);
    }
}
