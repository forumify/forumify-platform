<?php

declare(strict_types=1);

namespace Forumify\Core\Component\Table;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PreMount;

/**
 * @phpstan-type ColumnDef array{
 *     label: string|null,
 *     field: string|null,
 *     searchable: bool|null,
 *     sortable: bool|null,
 *     renderer: callable|null,
 *     class: string|null,
 * }
 */
abstract class AbstractTable
{
    public const SORT_ASC = 'ASC';
    public const SORT_DESC = 'DESC';

    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public int $limit = 20;

    /**
     * @var array<string, string>
     */
    #[LiveProp(writable: true)]
    public array $search = [];

    /**
     * @var array<string, "ASC"|"DESC"|null>
     */
    #[LiveProp]
    public array $sort = [];

    #[LiveProp]
    public string $class = '';

    /** @var array<string, ColumnDef> */
    private array $columns = [];
    private ?TableResult $result = null;
    private ColumnConfigurationProcessor $columnConfigurationProcessor;
    private UrlGeneratorInterface $urlGenerator;

    abstract protected function buildTable(): void;

    abstract protected function getData(int $limit, int $offset, array $search, array $sort): array;

    abstract protected function getTotalCount(array $search): int;

    #[Required]
    public function setColumnConfigurationProcessor(ColumnConfigurationProcessor $processor): void
    {
        $this->columnConfigurationProcessor = $processor;
    }

    #[Required]
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    #[LiveAction]
    public function setLimit(#[LiveArg] int $limit): void
    {
        $this->limit = $limit;
    }

    #[LiveAction]
    public function toggleSort(#[LiveArg] string $column): void
    {
        foreach (array_keys($this->sort) as $existingSort) {
            if ($existingSort !== $column) {
                /** @phpstan-ignore unset.possiblyHookedProperty */
                unset($this->sort[$existingSort]);
            }
        }

        $this->sort[$column] = match ($this->sort[$column] ?? null) {
            self::SORT_ASC => self::SORT_DESC,
            self::SORT_DESC => null,
            default => self::SORT_ASC,
        };
    }

    #[PreMount]
    public function populateSearchModel(): void
    {
        foreach ($this->getColumns() as $name => $column) {
            if ($column['searchable']) {
                $this->search[$name] = $this->search[$name] ?? '';
            }
        }
    }

    /**
     * @param array{
     *     label?: string,
     *     field?: string,
     *     searchable?: bool,
     *     sortable?: bool,
     *     renderer?: callable,
     *     class?: string,
     * } $column
     */
    protected function addColumn(string $name, array $column): static
    {
        $this->columns[$name] = $this->columnConfigurationProcessor->process($column);
        return $this;
    }

    /**
     * @return array<string, ColumnDef>
     */
    public function getColumns(): array
    {
        if (empty($this->columns)) {
            $this->buildTable();
        }

        return $this->columns;
    }

    public function getResult(): TableResult
    {
        if ($this->result !== null) {
            return $this->result;
        }

        $limit = $this->limit;
        $offset = ($this->page - 1) * $limit;
        $search = array_filter($this->search);
        $sort = array_filter($this->sort);

        $data = $this->getData($limit, $offset, $search, $sort);
        $rows = $this->transformData($data);

        $this->result = new TableResult(
            $rows,
            $this->getTotalCount($search),
        );
        return $this->result;
    }

    private function transformData(array $data): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $rows = [];
        foreach ($data as $rowData) {
            $row = [];
            foreach ($this->getColumns() as $name => $column) {
                $columnData = $column['field'] !== null
                    ? $propertyAccessor->getValue($rowData, $column['field'])
                    : null;

                if ($column['renderer'] !== null) {
                    $columnData = $column['renderer']($columnData, $rowData);
                }

                $row[$name] = $columnData;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    protected function renderAction(string $path, array $pathArguments, string $icon): string
    {
        $url = $this->urlGenerator->generate($path, $pathArguments);
        return "<a class='btn-link btn-icon btn-small' href='$url'><i class='ph ph-$icon'></i></a>";
    }
}
