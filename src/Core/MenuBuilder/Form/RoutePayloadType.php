<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Traversable;

class RoutePayloadType extends AbstractType implements DataMapperInterface
{
    private const ROUTE_NAME_BLACKLIST = [
        '/^_preview_error$/',
        '/^ux_/',
    ];

    private const ROUTE_PATH_BLACKLIST = [
        '/^\/admin\//'
    ];

    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $routeCollection = $this->router->getRouteCollection()->all();
        $routeChoices = array_filter($routeCollection, $this->isRouteChoice(...), ARRAY_FILTER_USE_BOTH);
        $routeChoices = array_keys($routeChoices);
        sort($routeChoices);

        $builder
            ->add('route', ChoiceType::class, [
                'choices' => array_combine($routeChoices, $routeChoices),
                'placeholder' => 'admin.menu_builder.route.select_route',
            ])
            ->add('parameters', TextareaType::class, [
                'help' => 'admin.menu_builder.route.parameters_help',
                'required' => false,
                'empty_data' => '{}'
            ])
            ->setDataMapper($this);
    }


    private function isRouteChoice(Route $route, string $name): bool
    {
        foreach (self::ROUTE_NAME_BLACKLIST as $blacklisted) {
            if (preg_match($blacklisted, $name) === 1) {
                return false;
            }
        }

        foreach (self::ROUTE_PATH_BLACKLIST as $blacklisted) {
            if (preg_match($blacklisted, $route->getPath()) === 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, string|array> $viewData
     * @param Traversable<FormInterface> $forms
     */
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        $formRows = iterator_to_array($forms);

        if (isset($viewData['route'])) {
            $formRows['route']->setData($viewData['route']);
            $formRows['parameters']->setData(json_encode($viewData['parameters'], JSON_PRETTY_PRINT));
        }
    }

    /**
     * @param Traversable<FormInterface> $forms
     * @param array<string, string|array> $viewData
     */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $formRows = iterator_to_array($forms);
        $viewData['route'] = $formRows['route']->getData();
        $viewData['parameters'] = json_decode($formRows['parameters']->getData());
    }
}
