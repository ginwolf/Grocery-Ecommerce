<?php
date_default_timezone_set("Asia/Manila");

class DB
{
    protected $db;

    /**
     * DB constructor.
     * @param $db
     * @param string $host
     * @param string $user
     * @param string $pass
     * @throws Exception
     */

    public function __construct($db, $host = "localhost", $user = "root", $pass = "")
    {
        $this->db = new mysqli($host, $user, $pass, $db);

        if ($this->db->connect_errno) {
            throw new Exception("Cannot connect to database: " . $this->db->connect_error);
        }
    }

    public function getLastId() {
        return $this->db->insert_id;
    }



    public function __destruct()
    {
        $this->db->close();
    }

    /**
     * Use to return all rows
     * @param $sql
     * @return mixed|null
     */
    public function findAll($sql)
    {
        $data = null;
        if ($res = $this->db->query($sql)) {
            $data = $res->fetch_all(MYSQLI_ASSOC);
            $res->close();
        }
        return $data;
    }

    /**
     * Use to find One Row
     * @param $sql
     * @return mixed|null
     */
    public function findOne($sql)
    {
        $data = null;
        if ($res = $this->db->query($sql)) {
            $data = $res->fetch_row();
        }
        return $data;
    }

    public function findCell($sql)
    {
        $data = null;
        if ($res = $this->db->query($sql)) {
            $value = $res->fetch_array(MYSQLI_NUM);
            $data = is_array($value) ? $value[0] : null;
        }
        return $data;
    }

    /**
     * Use to count rows
     * @param $sql
     * @return int
     */
    public function count($sql)
    {
        $data = 0;
        if ($res = $this->db->query($sql)) {
            $data = $res->fetch_row()[0];
        }
        return $data;
    }

    /**
     * @param $sql
     * @throws Exception
     */
    public function query($sql)
    {
        if (!$this->db->query($sql)) {
            throw new Exception("Error: " . $this->db->error);
        }
    }

    /**
     * Use prepared statements for select multiple rows
     * @param $sql
     * @param string $types
     * @param array $params
     * @return array|mixed
     * @throws Exception
     */
    public function preparedSelect($sql, $types = '', $params = null)
    {
        $data = [];
        $stmt = $this->db->stmt_init();
        if (!$stmt->prepare($sql)) {
            throw new Exception("Error: " . $stmt->error);
        }

        if (is_array($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception('Error: ' . $stmt->error);
        }
        return $data;
    }

    /**
     * @param $table
     * @param $data
     * @throws Exception
     */
    public function insertQueryMultiple($table, $data)
    {
        $firstRow = $data[0];

        $fields = "`" . implode("`,`", array_keys($firstRow)) . "`";
        $count = count($firstRow);
        $question = substr(str_repeat(",?", $count), 1);
        $values = substr(str_repeat(",(" . $question .")", count($data)), 1);
        $sqlstring = "insert into $table ($fields) values $values";

        $params = [];
        foreach ($data as $datum) {
            $params = array_merge($params,array_values($datum));
        }
        $this->preparedQuery($sqlstring, str_repeat('s', count($data) * count($firstRow)), $params);

    }

    /**
     * Use prepared statements for select one row
     * @param $sql
     * @param string $types
     * @param array $params
     * @return array|mixed
     * @throws Exception
     */
    public function preparedSelectOne($sql, $types = '', $params = null)
    {
        $data = null;
        $stmt = $this->db->stmt_init();
        if (!$stmt->prepare($sql)) {
            throw new Exception("Error: " . $stmt->error);
        }

        if (is_array($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
        } else {
            throw new Exception('Error: ' . $stmt->error);
        }
        return $data;
    }

    public function updateQuery($table, $data)
    {

        $sql_key = "";
        $sql_value = "";
        $cnt = count($data);
        $count = 1;
        $sql = "";
        $sql_index = "";
        foreach ($data as $key => $value) {
            if ($count == $cnt) {
                $sql_index = $key . "= ? ";
            } else {
                $sql = $sql . $key . "= ? ,";
            }
            $count++;
            $values[] = $value;
        }

        $sql_key = "update $table set $sql";
        $sql_key = substr($sql_key, 0, strlen($sql_key) - 1);
        $sql_string = $sql_key . " where " . $sql_index;

        $this->preparedQuery($sql_string, str_repeat('s', count($values)), $values);
    }

    public function updateQueryWhere($table, $data, $where)
    {

        $sql_key = "";
        $sql_value = "";
        $cnt = count($data);
        $count = 1;
        $sql = "";
        $sql_index = "";
        foreach ($data as $key => $value) {
            $sql = $sql . $key . "= ? ,";
            $count++;
            $values[] = $value;
        }

        if (count($where) > 0) {

            $sql_index        =     implode(', ', array_map(function ($v, $k) {
                return '`' . $k . '` = ?';
            }, $where, array_keys($where)));

            $values = array_merge($values, array_values($where));
        }


        $sql_key = "update $table set $sql";
        $sql_key = substr($sql_key, 0, strlen($sql_key) - 1);
        $sql_string = $sql_key . " where " . $sql_index;
        $this->preparedQuery($sql_string, str_repeat('s', count($values)), $values);
    }

    public function insertQuery($table, $data)
    {
        $sql_key = "";
        $sql_value = "";

        $fields = "";
        $question = "";
        $values = [];
        foreach ($data as $key => $value) {
            $fields = $fields . $key . ",";
            $question = $question . "?" . ",";
            $values[] = $value;
        }
        $fields = substr($fields, 0, strlen($fields) - 1);
        $question = substr($question, 0, strlen($question) - 1);

        $sqlstring = "insert into $table ($fields) values ($question)";

        $this->preparedQuery($sqlstring, str_repeat('s', count($values)), $values);
        // return  $this->db->lastInsertId();

        return $this->findCell("select last_insert_id()");

    }

    /**
     * Use prepared statements for update and delete
     * @param $sql
     * @param string $types
     * @param array $params
     * @throws Exception
     */
    public function preparedQuery($sql, $types = '', $params = null)
    {
        /** 1. init statement */
        $stmt = $this->db->stmt_init();
        /** 2. prepare statement */
        if (!$stmt->prepare($sql)) {
            throw new Exception("Error: " . $stmt->error);
        }
        /** 3. bind statement */
        if (is_array($params)) {
            $stmt->bind_param($types, ...$params);
        }

        /** 4. execute statement */
        if (!$stmt->execute()) {
            throw new Exception("Error: " . $stmt->error);
        }
    }

    /**
     * Use to begin transaction
     */
    public function beginTransaction()
    {
        $this->db->autocommit(false);
    }
    /**
     * Use to end transaction
     */
    public function endTransaction()
    {
        $this->db->commit();
    }
    /**
     * Use to rollback transaction
     */
    public function rollbackTransaction()
    {
        $this->db->rollback();
    }
}
