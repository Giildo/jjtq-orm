<?php

namespace Jojotique\ORM\Classes;

class ORMSelectJoinTable
{
    /**
     * @var ORMModel
     */
    private $ORMModel;

    /**
     * ORMSelectJoinTable constructor.
     * @param ORMModel $ORMModel
     */
    public function __construct(ORMModel $ORMModel)
    {
        $this->ORMModel = $ORMModel;
    }

    /**
     * @param array $tables
     * @param string $parentColumn
     * @param int $parentValue
     * @return array
     */
    public function select(array $tables, string $parentColumn, int $parentValue): array
    {
        $parentTable = $childTable = null;
        foreach ($tables as $parent => $child) {
            $parentTable = $parent;
            $childTable = $child;
        }

        $table = $parentTable . ucfirst($childTable);
        return $this->ORMModel->ORMFind(
            "SELECT * FROM {$table} WHERE {$parentColumn} = :{$parentColumn}",
            \stdClass::class, [$parentColumn => $parentValue]
        );
    }

    /**
     * @param array $tables
     * @param array $columns
     * @param array $values
     * @return bool
     */
    public function jointExists(array $tables, array $columns, array $values): bool
    {
        $parentValue = $childValue = null;
        $parentTable = $childTable = null;
        $parentColumn = $childColumn = null;
        foreach ($values as $parent => $child) {
            $parentValue = (int)$parent;
            $childValue = (int)$child;
        }

        foreach ($tables as $parent => $child) {
            $parentTable = $parent;
            $childTable = $child;
        }

        foreach ($columns as $parent => $child) {
            $parentColumn = $parent;
            $childColumn = $child;
        }

        $table = $parentTable . ucfirst($childTable);
        $results = $this->ORMModel->ORMFind(
            "SELECT * FROM {$table} WHERE {$parentColumn} = :{$parentColumn} AND {$childColumn} = :{$childColumn}",
            '', [$parentColumn => $parentValue, $childColumn => $childValue]
        );

        return (empty($results)) ? false : true;
    }

    /**
     * @param array $tables
     * @param array $values
     */
    public function save(array $tables, array $values): void
    {
        $parentValue = $childValue = null;
        $parentTable = $childTable = null;
        foreach ($values as $parent => $child) {
            $parentValue = (int)$parent;
            $childValue = (int)$child;
        }

        foreach ($tables as $parent => $child) {
            $parentTable = $parent;
            $childTable = $child;
        }

        $table = $parentTable . ucfirst($childTable);
        $this->ORMModel->ORMInsert("INSERT INTO {$table} VALUES ($parentValue, $childValue)");
    }

    public function delete(array $tables, array $columns, array $values)
    {
        $table = null;
        foreach ($tables as $parent => $child) {
            $table = $parent . ucfirst($child);
        }

        $this->ORMModel->ORMDeleteJointsTables($table, $columns, $values);
    }
}
