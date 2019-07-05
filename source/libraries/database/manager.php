<?php
/*
#-----------------------------------------
| b1 database manager
| https://beranek1.github.io/webanalytics/
#-----------------------------------------
| made by beranek1
| https://github.com/beranek1
#-----------------------------------------
*/

class b1_db_manager {
    public $connected = false;
    private $connection = null;
    private $user = null;
    private $password = null;
    private $database = null;
    private $host = null;
    private $type = null;

    function get_row_count($query) {
        $count = 0;
        $result = $this->connection->query($query);
        if($result instanceof mysqli_result) {
            if($row = $result->fetch_row()) {
                $count = intval($row[0]);
            }
            $result->close();
        }
        return $count;
    }
    
    function get_rows_array($query) {
        $rows = array();
        $result = $this->connection->query($query);
        if($result instanceof mysqli_result) {
            while($row = $result->fetch_row()) {
                $rows[] = $row;
            }
            $result->close();
        }
        return $rows;
    }
    
    function get_one_row($query) {
        $row0 = null;
        $result = $this->connection->query($query);
        if($result instanceof mysqli_result) {
            if($row = $result->fetch_row()) {
                $row0 = $row;
            }
            $result->close();
        }
        return $row0;
    }
    
    function generate_id($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, $charactersLength - 1)];
        }
        return $id;
    }
    
    function generate_query($ary, $id, $field = "id") {
        $keys = $field;
        $values = "'".$id."'";
        foreach ($ary as $key => $value) {
            if($value != null) {
                $keys .= ", ".$key."";
                $values .= ", '".$value."'";
            }
        }
        return array("keys" => $keys, "values" => $values);
    }
    
    function ex_gen_query($table, $ary, $id, $field = "id") {
        $query = $this->generate_query($ary, $id, $field);
        if(!$this->connection->query("INSERT INTO ".$table." (".$query["keys"].") VALUES (".$query["values"].");")) {
            error_log("".$this->connection->error."\n");
        }
    }

    function query($query) {
        return $this->connection->query($query);
    }

    function connect() {
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->database);
        if($this->connection->connect_errno) {
            error_log("Error: ".$this->connection->error."\n");
            $this->connected = false;
        } else {
            $this->connected = true;
        }
    }

    function close() {
        if($this->connected) {
            $this->connection->close();
        }
    }

    function __construct($user = "root", $password = "", $database = "", $host = "localhost", $type = "mysql") {
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->host = $host;
        $this->type = $type;
        $this->connect();
    } 
}