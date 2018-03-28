<?php

namespace Jojotique\ORM\Classes;

class ORMSelectJoinTable
{
    /**
     * @var string
     */
    private $configFiles;

    /**
     * @var string
     */
    private $parentTable;

    /**
     * @var string
     */
    private $childTable;

    /**
     * @var ORMModel
     */
    private $ORMModel;

    /**
     * ORMSelectJoinTable constructor.
     * @param string $configFiles
     * @param ORMModel $ORMModel
     */
    public function __construct(string $configFiles, ORMModel $ORMModel)
    {
        $this->configFiles = $configFiles;
        $this->ORMModel = $ORMModel;
    }

    public function select(array $tables): void
    {
        foreach ($tables as $parentTable => $childTable) {
            $this->parentTable = $parentTable;
            $this->childTable = $childTable;
        }
    }

    /**
     * @param array $values
     * @return bool
     */
    public function jointExists(array $values): bool
    {
        $parentValue = $childValue = null;
        foreach ($values as $parent => $child) {
            $parentValue = $parent;
            $childValue = $child;
        }

        $table = $this->parentTable . ucfirst($this->childTable);
        $tableParentId = $this->parentTable . 'Id';
        $tableChildId = $this->childTable . 'Id';
        $results = $this->ORMModel->ORMFind("SELECT * FROM {$table} WHERE {$tableParentId} = :{$tableParentId} AND {$tableChildId} = :{$tableChildId}",
            '', [$tableChildId => $childValue, $tableParentId => $parentValue]);

        return true;
    }
}
