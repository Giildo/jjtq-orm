<?php

namespace Jojotique\ORM\Classes;

use Jojotique\ORM\Interfaces\ORMModelInterface;
use PDO;

class ORMModel implements ORMModelInterface
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * ORMModel constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère un statement pour ajouter un élément dans la base de données
     *
     * @param string $statement
     * @return void
     */
    public function ORMInsert(string $statement): void
    {
        $this->pdo->query($statement);
    }

    /**
     * Récupère un statement pour modifier un élément dans la base de données
     *
     * @param string $statement
     * @param string $primaryKey
     * @param $primaryKeyValue
     * @return void
     */
    public function ORMUpdate(string $statement, string $primaryKey, $primaryKeyValue): void
    {
        $results = $this->pdo->prepare($statement);
        $results->bindParam($primaryKey, $primaryKeyValue);
        $results->execute();
    }

    /**
     * Récupère les colonnes dans la base de données et les retourne
     *
     * @return array
     */
    public function ORMShowColumns(): array
    {
        $results = $this->pdo->query("SHOW COLUMNS FROM {$this->table}");
        return $results->fetchAll();
    }

    /**
     * Crée une table dans la base de données
     *
     * @param string $statement
     * @return void
     */
    public function ORMCreateTable(string $statement): void
    {
        $this->pdo->query("CREATE TABLE IF NOT EXISTS {$this->table} " . $statement . " ENGINE=INNODB");
    }

    /**
     * @param string $statement
     * @param string|null $entityType
     * @param array|null $whereOptions
     * @param bool|null $inOption
     * @return array
     */
    public function ORMFind(string $statement, ?string $entityType = null, ?array $whereOptions = [], ?bool $inOption = false): array
    {
        $result = $this->pdo->prepare($statement);

        if (!is_null($entityType)) {
            $result->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $entityType);
        }

        $i = 0;
        if (!empty($whereOptions)) {
            foreach ($whereOptions as $key => $values) {
                if (preg_match('#\.#', $key)) {
                    $results = explode('.', $key);
                    $key = $results[0] . ucfirst(strtolower($results[1]));
                }

                if ($inOption) {
                    foreach ($values as $value) {
                        $i++;
                        $key2 = $key . $i;
                        $result->bindValue($key2, $value);
                    }
                } else {
                    $result->bindValue($key, $values);
                }
            }
        }

        $result->execute();
        return $result->fetchAll();
    }

    public function ORMDelete(ORMEntity $entity): void
    {
        $result = $this->pdo->prepare("DELETE FROM {$entity->tableName} WHERE id=:id");
        $result->bindValue('id', $entity->id);
        $result->execute();
    }

    public function ORMDeleteJointsTables(string $tableName, array $columns, array $values): void
    {
        $parentValue = $childValue = null;
        $parentColumn = $childColumn = null;
        foreach ($values as $parent => $child) {
            $parentValue = (int)$parent;
            $childValue = (int)$child;
        }

        foreach ($columns as $parent => $child) {
            $parentColumn = $parent;
            $childColumn = $child;
        }

        $result = $this->pdo->prepare("DELETE FROM {$tableName} WHERE {$parentColumn} = :{$parentColumn} AND {$childColumn} = :{$childColumn}");
        $result->bindValue($parentColumn, $parentValue);
        $result->bindValue($childColumn, $childValue);
        $result->execute();
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }
}
