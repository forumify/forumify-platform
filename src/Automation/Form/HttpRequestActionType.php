<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Forumify\Core\Form\CodeEditorLanguage;
use Forumify\Core\Form\CodeEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class HttpRequestActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endpoint', TextType::class)
            ->add('method', ChoiceType::class, [
                'choices' => [
                    'GET' => 'get',
                    'POST' => 'post',
                    'PUT' => 'put',
                    'PATCH' => 'patch',
                    'DELETE' => 'delete',
                ],
            ])
            ->add('contentType', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'application/json',
                ],
            ])
            ->add('body', CodeEditorType::class, [
                'required' => false,
                'language' => CodeEditorLanguage::Twig->value,
                'help' => 'admin.automations.action.http_request.body_help',
            ])
        ;
    }
}
