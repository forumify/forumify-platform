<?php

declare(strict_types=1);

namespace Forumify\Core\Component\Table;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;

class ColumnConfigurationProcessor
{
    private readonly NodeInterface $tree;
    private readonly Processor $processor;

    public function __construct()
    {
        $treebuilder = new TreeBuilder('column');

        //@formatter:off
        $root = $treebuilder->getRootNode();
        $root
            ->children()
                ->scalarNode('label')
                    ->defaultNull()
                ->end()
                ->scalarNode('field')
                    ->defaultNull()
                ->end()
                ->booleanNode('searchable')
                    ->defaultTrue()
                ->end()
                ->booleanNode('sortable')
                    ->defaultTrue()
                ->end()
                ->variableNode('renderer')
                    ->defaultNull()
                ->end()
                ->scalarNode('class')
                    ->defaultNull()
                ->end()
            ->end();
        //@formatter:on

        $this->tree = $treebuilder->buildTree();
        $this->processor = new Processor();
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function process(array $config): array
    {
        return $this->processor->process($this->tree, [$config]);
    }
}
