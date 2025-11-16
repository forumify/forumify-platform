<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Forumify\Core\Form\CodeEditorLanguage;
use Forumify\Core\Form\CodeEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<string, mixed>>
 */
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
            ->add('headers', TextareaType::class, [
                'required' => false,
                'help' => 'admin.automations.action.http_request.headers_help',
                'help_html' => true,
                'attr' => [
                    'placeholder' => 'Content-Type: application/json',
                ],
            ])
            ->add('body', CodeEditorType::class, [
                'required' => false,
                'language' => CodeEditorLanguage::Twig->value,
                'help' => 'admin.automations.action.http_request.body_help',
                'help_html' => true,
            ])
        ;
    }
}
