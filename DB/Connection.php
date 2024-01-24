<?php

namespace DB;


use PHPUnit\Framework\Attributes\IgnoreMethodForCodeCoverage;

class Connection
{
    public \mysqli $connection;

    private function connect()
    {
        $hostname = array_key_exists('DB_HOST', $_ENV)? $_ENV['DB_HOST']: 'db';
        $db_name = array_key_exists('DB_NAME', $_ENV)? $_ENV['DB_NAME']: 'localhost';
        $db_password = array_key_exists('DB_PASSWORD', $_ENV)? $_ENV['DB_PASSWORD']: 'localhost';
        $db_user = array_key_exists('DB_USER', $_ENV)? $_ENV['DB_USER']: 'localhost';
        $db_port = array_key_exists('DB_PORT', $_ENV)? $_ENV['DB_PORT']: '3308';
        $this->connection = new \mysqli($hostname, $db_user, $db_password, $db_name, $db_port);
    }

    public function __construct()
    {
        $this->connect();
    }

    private function execute_query($query): \mysqli_result|bool
    {
        return $this->connection->query($query);
    }


    /*
     * @codeCoverageIgnore
     * */
    private function prepare_insert_query(string $table_name, array $data, bool $ignore=false): string
    {
        $query = "INSERT ".($ignore?"IGNORE ": "")."INTO $table_name";
        $param_names = '('.implode(', ', array_keys($data)).') ';
        $param_values = 'VALUES(';
        foreach ($data as $column=>$value) {
            $value_type = gettype($value);
            if (in_array($value_type, ['float', 'int'])) {
                $param_values .= ($value.', ');
            }
            elseif ($value_type === 'boolean') {
                $param_values .= ((int)$value.', ');
            }
            else {
                $param_values .= ('"'.$value.'"'.', ');
            }
        }
        $param_values = rtrim($param_values,', ').')';
        return $query.$param_names.$param_values;
    }


    private function prepare_filters(array $filters): string
    {
        $filter_string = '';
        foreach ($filters as $filter) {
            if (sizeof($filter) != 3) {
                throw new \ValueError('filter should contain 3 parameters: column_name, function and value');
            }
            $value = $filter[2];
            if (gettype($value) === 'array') {
                if (sizeof($value) > 0 && gettype($value[0]) === 'string') {
                    $value = '("'.implode('", "', $value).'")';
                }
                else {
                    $value = '('.implode(', ', $value).')';
                }
            }
            elseif (gettype($value) === 'string') {
                $value = '"'.$value.'"';
            }
            $filter_string .= ($filter[0].' '.$filter[1].' '.$value);
        }
        return $filter_string;
    }

    private function prepare_update_query(string $table_name, $data, array|null $filters=null): string
    {
        $query = "UPDATE $table_name SET ";
        $param_values = '';
        foreach ($data as $column=>$value) {
            $value_type = gettype($value);
            if (in_array($value_type, ['float', 'integer'])) {
                $param_values .= ($column.'='.$value.', ');
            }
            elseif ($value_type === 'boolean') {
                $param_values .= ($column.'='.(int)$value.', ');
            }
            else {
                $param_values .= ($column.'='.'"'.$value.'"'.', ');
            }
        }
        $param_values = rtrim($param_values,', ');
        if (is_array($filters)) {
            $param_values .= (' WHERE '.$this->prepare_filters($filters));
        }
        return $query.$param_values;
    }

    public function insert(string $table_name, array $data): \mysqli_result|bool
    {
        return $this->execute_query($this->prepare_insert_query($table_name, $data));
    }

    public function insertOrIgnore(string $table_name, array $data): \mysqli_result|bool
    {
        return $this->execute_query($this->prepare_insert_query($table_name, $data, true));
    }

    public function update(string $table_name, array $data, null|array $filters=null): \mysqli_result|bool
    {
        return $this->execute_query($this->prepare_update_query($table_name, $data, $filters));
    }

    public function delete(string $table_name, null|array $filters=null): \mysqli_result|bool
    {
        return $this->execute_query($this->prepare_delete_query($table_name, $filters));
    }

    public function select(string $query): \mysqli_result|bool
    {
        return $this->execute_query($query);
    }

    private function prepare_select_query(string $table_name,
                                          null|array $columns=null,
                                          array|null $filters=null,
                                          array|null $joins=null): string
    {
        $query = 'SELECT ';
        if (is_array($columns) && sizeof($columns) > 0) {
            $query .= implode(', ', $columns);
        }
        else {
            $query .= '*';
        }
        $query .= (' FROM '.$table_name.' ');
        if (is_array($joins) && sizeof($joins) > 0) {
            $query .= implode(' ', $joins);
        }
        if (is_array($filters)) {
            $query .= (' WHERE '.$this->prepare_filters($filters));
        }
        return $query;
    }

    public function prepare_delete_query(string $table_name, array|null $filters=null): string
    {
        $query = 'DELETE FROM ';
        $query .= $table_name;
        if (is_array($filters)) {
            $query .= (' WHERE '.$this->prepare_filters($filters));
        }
        return $query;
    }

    public function select_one(
        string $table_name,
        null|array $columns=null,
        array|null $filters=null,
        array|null $joins=null
    ): bool|array|null
    {
        $query = $this->prepare_select_query($table_name, $columns, $filters, $joins);
        $result = $this->select($query);
        return $result->fetch_assoc();
    }

    public function select_all(
        string $table_name,
        null|array $columns=null,
        array|null $filters=null,
        array|null $joins=null): array
    {
        $query = $this->prepare_select_query($table_name, $columns, $filters, $joins);
        $result = $this->select($query);
        $value_arr = [];
        while ($value = $result->fetch_assoc()) {
            $value_arr[] = $value;
        }
        return $value_arr;
    }

    public function last_insert_id(): int|string
    {
        return $this->connection->insert_id;
    }
}