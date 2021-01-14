<?php

/*
 * Symfony DataTables Bundle
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Omines\DataTablesBundle\Adapter\Doctrine\ORM;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTableState;

/**
 * SearchCriteriaProvider.
 *
 * @psalm-import-type SearchColumn from DataTableState
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class SearchCriteriaProvider implements QueryBuilderProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(QueryBuilder $queryBuilder, DataTableState $state)
    {
        $this->processSearchColumns($queryBuilder, $state);
        $this->processGlobalSearch($queryBuilder, $state);
    }

    private function processSearchColumns(QueryBuilder $queryBuilder, DataTableState $state)
    {
        /** @var int */
        static $paramCounter = 0; // Prevent parameter names from conflicting
        foreach ($state->getSearchColumns() as $searchInfo) {
            $column = $searchInfo['column'];
            $search = $searchInfo['search'];

            if ('' !== trim($search)) {
                if (null !== ($filter = $column->getFilter())) {
                    $search = $filter->getValue($search);
                    if ($search === null) {
                        continue;
                    }
                }
                $paramName = ':_' . $column->getName() . ++$paramCounter;
                $queryBuilder->andWhere($column->getComparison($paramName, $search));
                $queryBuilder->setParameter($paramName, $search);
            }
        }
    }

    private function processGlobalSearch(QueryBuilder $queryBuilder, DataTableState $state)
    {
        /** @var int */
        static $paramCounter = 0; // Prevent parameter names from conflicting
        if (!empty($globalSearch = $state->getGlobalSearch())) {
            $expr = $queryBuilder->expr();
            $comparisons = $expr->orX();
            foreach ($state->getDataTable()->getColumns() as $column) {
                if ($column->isGlobalSearchable() && $column->isValidForSearch($globalSearch)) {
                    $search = $globalSearch; // Copy so passing by reference doesn't modify original
                    $paramName = ':_' . $column->getName() . ++$paramCounter;
                    $comparisons->add($column->getComparison($paramName, $search));
                    $queryBuilder->setParameter($paramName, $search);
                }
            }
            $queryBuilder->andWhere($comparisons);
        }
    }
}
