<?php

namespace App\Repository\RepositoryTrait;

use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\Paginator;

trait FilterRepositoryTrait
{
    private function addTextSearch($qb, $filter)
    {
        $expr = $qb->expr();
        $alias = current($qb->getRootAliases());

        $expressions = [];
        foreach ($filter as $fieldName => $fieldText) {
            if ($fieldText == '') continue;
            $expressions[] = $expr->like("{$alias}.{$fieldName}", ":{$fieldName}_text_search");
        }

        foreach ($filter as $fieldName => $fieldText) {
            if ($fieldText == '') continue;
            $qb->setParameter("{$fieldName}_text_search", "%{$fieldText}%");
        }

        $orx = call_user_func_array([$expr, 'orx'], $expressions);

        return $qb->andWhere($orx);
    }

    private function getFilterArrayByFieldNames(array $fieldNames, $searchString)
    {
        $filter = [];
        foreach ($fieldNames as $fieldName) {
            $filter[$fieldName] = $searchString;
        }

        return $filter;
    }

    private function addOrdering($qb, array $order)
    {
        $alias = current($qb->getRootAliases());

        foreach ($order as $orderKey => $orderDirection) {
            $qb->orderBy("{$alias}.{$orderKey}", $orderDirection);
        }

        return $qb;
    }

    public function paginate($query, $page, $pageSize)
    {
        $paginator = new Paginator();
        $paginate = $paginator->paginate($query, $page, $pageSize);

        return $paginate;
    }
}
