<?php

namespace Models;

require_once 'DB/Connection.php';

use DB\Connection;

abstract class AbstractModel
{
    protected Connection $connection;

    protected string $table_name;

    public function __construct()
    {
        $this->connection = new Connection();
    }

    public function insert(array $data): int|string|null
    {
        $result = $this->connection->insert($this->table_name, $data);
        if ($result === true) {
            return $this->connection->last_insert_id();
        }
        return null;
    }

    public function insertOrIgnore(array $data): int|string|null
    {
        $result = $this->connection->insertOrIgnore($this->table_name, $data);
        if ($result === true) {
            return $this->connection->last_insert_id();
        }
        return null;
    }

    public function update(array $data, null|array $filters=null): \mysqli_result|bool
    {
        return $this->connection->update($this->table_name, $data, $filters);
    }

    public function delete(null|array $filters=null): \mysqli_result|bool
    {
        return $this->connection->delete($this->table_name, $filters);
    }

    public function select_one(null|array $columns=null, array|null $filters=null, array|null $joins=null): bool|array|null
    {
        return $this->connection->select_one($this->table_name, $columns, $filters);
    }

    public function select_all(null|array $columns=null, array|null $filters=null, array|null $joins=null): bool|array|null
    {
        return $this->connection->select_all($this->table_name, $columns, $filters, $joins);
    }


}