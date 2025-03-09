<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

use Forumify\Cms\Form\ResponsiveColumnSizeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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

    public function getSettingsForm(array $data = []): ?FormInterface
    {
        $colCount = $this->getColumnCount();

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
            ->getForm()
        ;
    }
}
