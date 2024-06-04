<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Forumify\Core\Form\RichTextEditorType;
use Forumify\Forum\Entity\Forum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TopicType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'forum' => null,
            'data_class' => TopicData::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $forumType = $options['forum']?->getType();

        $builder->add('title', TextType::class);
        if ($forumType !== Forum::TYPE_TEXT) {
            $builder->add('image', FileType::class, [
                'required' => $forumType === Forum::TYPE_IMAGE,
            ]);
        }

        if (empty($options['data'])) {
            $builder->add('content', RichTextEditorType::class);
        }
    }
}
