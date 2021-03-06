<?php

namespace PHPMinds\Repository;

use PHPMinds\Model\Db;

class RepositoryAbstract
{
    /**
     * @var \PDO
     */
    protected $db;

    protected $table;

    protected $columns = [];

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    protected function getById($id)
    {
        $sql = "SELECT {$this->getColumns()} " .
                "FROM {$this->table} " .
                "WHERE id=:id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":id", $id, \PDO::PARAM_INT);

        $stmt->execute();
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        return $stmt->fetch() ?: [];
    }

    protected function getColumns()
    {
        return implode(', ', $this->columns);
    }

    public function getAll($returnType = \PDO::FETCH_OBJ)
    {
        $sql = "SELECT {$this->getColumns()} ".
                "FROM {$this->table} ";

        $events = $this->db->query($sql)->fetchAll($returnType);

        $result = array_reduce($events, function($carry, $item){

            if (is_object($item)) {
                $carry[$item->id] = $item;
            } else {
                $carry[$item['id']] = $item;
            }
            return $carry;
        });

        return $result;
    }
}
