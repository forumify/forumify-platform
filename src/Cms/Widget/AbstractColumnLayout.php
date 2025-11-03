<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Forumify\Cms\Form\ResponsiveColumnSizeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;

abstract class AbstractColumnLayout extends AbstractWidget
{
    abstract protected function getColumnCount(): int;

    public function getCategory(): string
    {
        return 'layout';
    }

    public function getPreview(): string
    {
        $colCount = $this->getColumnCount();
        $html = '<div class="grid-' . $colCount . ' gap-2 h-100">';
        for ($i = 0; $i < $colCount; $i++) {
            $html .= '<div class="col-1 widget-slot"></div>';
        }
        $html .= '</div>';
        return $html;
    }

    public function getTemplate(): string
    {
        return '@Forumify/frontend/cms/widgets/columns.html.twig';
    }

    /**
     * @param array<string, mixed> $data
     * @return FormInterface<array<string, mixed>|null>
     */
    public function getSettingsForm(array $data = []): ?FormInterface
    {
        $colCount = $this->getColumnCount();

        $gaps = [0,1,2,3,4,5,6,7,8,9,10,11,12];

        return $this->createForm($data)
            ->add('columnCount', HiddenType::class, [
                'data' => $colCount
            ])
            ->add('responsive', CheckboxType::class, [
                'required' => false,
                'data' => (bool)($data['responsive'] ?? false),
            ])
            ->add('columns', ResponsiveColumnSizeType::class, [
                'columns' => $colCount,
                'help' => 'admin.cms.widget.responsive.column_help'
            ])
            ->add('gap', ChoiceType::class, [
                'choices' => array_combine($gaps, $gaps)
            ])
            ->getForm()
        ;
    }
}
