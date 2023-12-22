<?php

declare(strict_types=1);

namespace Forumify\Core\Component\Table;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PreMount;

abstract class AbstractTable
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public int $limit = 20;

    #[LiveProp(writable: true)]
    public array $search = [];

    /** @var array<array> */
    private array $columns = [];
    private ?TableResult $result = null;
    private ColumnConfigurationProcessor $columnConfigurationProcessor;

    abstract protected function buildTable(): void;

    abstract protected function getData(int $limit, int $offset, array $search): array;

    abstract protected function getTotalCount(array $search): int;

    #[Required]
    public function setColumnConfigurationProcessor(ColumnConfigurationProcessor $processor): void
    {
        $this->columnConfigurationProcessor = $processor;
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

    #[PreMount]
    public function populateSearchModel(): void
    {
        foreach ($this->getColumns() as $column) {
            if ($column['searchable']) {
                $columnName = $column['name'];
                $this->search[$columnName] = $this->search[$columnName] ?? '';
            }
        }
    }

    protected function addColumn(array $column): static
    {
        $this->columns[] = $this->columnConfigurationProcessor->process($column);
        return $this;
    }

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
        $data = $this->getData($limit, $offset, $this->search);
        $rows = $this->transformData($data);

        $this->result = new TableResult(
            $rows,
            $this->getTotalCount($this->search),
        );
        return $this->result;
    }

    private function transformData(array $data): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $rows = [];
        foreach ($data as $rowData) {
            $row = [];
            foreach ($this->getColumns() as $column) {
                $columnData = $column['field'] !== null
                    ? $propertyAccessor->getValue($rowData, $column['field'])
                    : $rowData;

                if ($column['renderer'] !== null) {
                    $columnData = $column['renderer']($columnData);
                }

                $row[$column['name']] = $columnData;
            }
            $rows[] = $row;
        }

        return $rows;
    }
}
