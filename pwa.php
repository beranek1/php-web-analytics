<?php
/*
#-----------------------------------------
| PHP-Web-Analytics
| https://webanalytics.one
#-----------------------------------------
| made by beranek1
| https://github.com/beranek1
#-----------------------------------------
*/

class pwa {
    private $user = null;
    private $data = null;


}

class pwa_browser {
    private $database = null;
    private $unique_id = null;
    private $ip_address = null;
    private $country = null;
    private $accept_language = null;
    private $user_agent = null;
    private $profile_id = null;
    private $last_update = null;
    private $time_created = null;

    function get_unique_id() {
        return $this->unique_id;
    }

    function get_ip_address() {
        return $this->ip_address;
    }

    function get_country() {
        return $this->country;
    }

    function get_language() {
        return $this->accept_language != null ? substr($this->accept_language, 0, 2) : null;
    }

    function get_accept_language() {
        return $this->accept_language;
    }

    function get_user_agent() {
        return $this->user_agent;
    }

    function get_profile_id() {
        return $this->profile_id;
    }

    function get_last_update() {
        return $this->last_update;
    }

    function get_time_created() {
        return $this->time_created;
    }

    function check() {
        if($this->database->first("wa_browsers", "id", ["id" => $this->get_unique_id()]) == null) {
            $this->db_manager->add("wa_browsers", [
                "id" => $this->get_unique_id(),
                "ip" => $this->get_ip_address(),
                "country" => $this->get_country(),
                "language" => $this->get_language(),
                "accept_language" => $this->get_accept_language(),
                "user_agent" => $this->get_user_agent(),
                "profile_id" => $this->get_profile_id()
            ]);
        }
    }
    function __construct(pwa_db $database,
                         $unique_id,
                         $ip_address,
                         $country,
                         $accept_language,
                         $user_agent,
                         $profile_id,
                         $last_update,
                         $time_created)
    {
        $this->database = $database;
        $this->unique_id = $unique_id;
        $this->ip_address = $ip_address;
        $this->country = $country;
        $this->accept_language = $accept_language;
        $this->user_agent = $user_agent;
        $this->profile_id = $profile_id;
        $this->last_update = $last_update;
        $this->time_created = $time_created;
    }
}

class pwa_data {
    private $database = null;

    // Create required tables in given database if not existing
    function check_database() {
        $this->database->create_table("wa_ips", [
            "ip" => "VARCHAR(45) PRIMARY KEY",
            "host" => "VARCHAR(253)",
            "country" => "VARCHAR(2)",
            "isp" => "VARCHAR(127)",
            "last_update" => "TIMESTAMP NULL"
        ]);
//        $this->database->create_table("wa_profiles", [
//            "id" => "VARCHAR(20) PRIMARY KEY",
//            "screen_width" => "VARCHAR(9)",
//            "screen_height" => "VARCHAR(9)",
//            "interface_width" => "VARCHAR(9)",
//            "interface_height" => "VARCHAR(9)",
//            "color_depth" => "VARCHAR(7)",
//            "pixel_depth" => "VARCHAR(7)",
//            "cookies_enabled" => "VARCHAR(5)",
//            "java_enabled" => "VARCHAR(5)"
//        ]);
        $this->database->create_table("wa_trackers", [
            "id" => "VARCHAR(20) PRIMARY KEY",
            "domain" => "TEXT",
            "browser_id" => "VARCHAR(15) NOT NULL",
            "user_agent" => "TEXT"
        ]);
        $this->database->create_table("wa_browsers", [
            "id" => "VARCHAR(20) PRIMARY KEY",
            "ip" => "VARCHAR(45) NOT NULL",
            "country" => "VARCHAR(2)",
            "language" => "VARCHAR(2)",
            "accept_language" => "TEXT",
            "user_agent" => "TEXT",
            "profile_id" => "VARCHAR(10)",
            "last_update" => "TIMESTAMP NULL"
        ]);
        $this->database->create_table("wa_sessions", [
            "id" => "VARCHAR(20) PRIMARY KEY",
            "browser_id" => "VARCHAR(15)",
            "last_update" => "TIMESTAMP NULL"
        ]);
        $this->database->create_table("wa_requests", [
            "id" => "VARCHAR(20) PRIMARY KEY",
            "accept" => "TEXT",
            "protocol" => "TEXT",
            "port" => "INT(6)",
            "host" => "VARCHAR(253)",
            "uri" => "TEXT",
            "referrer" => "TEXT",
            "visitor_ip" => "VARCHAR(45)",
            "visitor_country" => "VARCHAR(2)",
            "cf_ray_id" => "TEXT",
            "user_agent" => "TEXT",
            "language" => "VARCHAR(2)",
            "accept_language" => "TEXT",
            "browser_id" => "VARCHAR(15)",
            "session_id" => "VARCHAR(20)"
        ]);
    }

    // Constructor
    function __construct(pwa_db $database)
    {
        $this->database = $database;
        $this->check_database();
    }
}

class pwa_db {
    // Boolean to check whether a database connection has been established
    public $connected = false;

    // Database Connection
    private $connection = null;

    // Database user credentials
    private $user = null;
    private $password = null;

    // Database information
    private $dsn = null;

    // Convert the given array to a SQL filter
    function get_filter($filter) {
        if($filter == null) {
            return "";
        }
        $query = " WHERE ";
        $i = 1;
        foreach ($filter as $key => $value) {
            if(isset($value)) {
                $query .= "`".$key."` = '".strval($value)."'";
            } else {
                $query .= "`".$key."` IS NULL";
            }
            if($i != count($filter)) {
                $query .= " AND ";
            }
            $i++;
        }
        return $query;
    }

    // Count the elements in the table that match the filter
    function count($table, $filter = null) {
        $result = $this->get_one_row("SELECT COUNT(*) FROM `".$table."`".$this->get_filter($filter).";");
        return $result[0];
    }

    // Get the first row that matches the query
    function get_one_row($query) {
        foreach ($this->query($query) as $row) {
            return $row;
        }
        return null;
    }

    // Get the first row that matches the query
    function first($table, $keys, $filter) {
        return $this->get_one_row("SELECT ".$keys." FROM ".$table."".$this->get_filter($filter)." LIMIT 1;");
    }

    // Generate unique identifier
    function generate_id($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, $charactersLength - 1)];
        }
        return $id;
    }

    // Add row to table
    function add($table, $ary) {
        $keys = "";
        $values = "";
        $i = 1;
        foreach ($ary as $key => $value) {
            if($value != null) {
                if($i != 1) {
                    $keys .= ", ";
                    $values .= ", ";
                }
                $keys .= "`".$key."`";
                $values .= "'".strval($value)."'";
                $i++;
            }
        }
        $this->query("INSERT INTO ".$table." (".$keys.") VALUES (".$values.");");
    }

    // Delete rows that match the filter
    function delete($table, $filter) {
        $this->query("DELETE FROM ".$table."".$this->get_filter($filter).";");
    }

    // Execute query
    function query($query) {
        return $this->connection->query($query);
    }

    // Create table with given name and fields
    function create_table($name, $keys) {
        $query = "CREATE TABLE IF NOT EXISTS `".$name."` (";
        foreach ($keys as $key => $value) {
            $query .= "`".$key."` ".$value.", ";
        }
        $query .= "`time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);";
        $this->query($query);
    }

    // Update given fields of rows that match filter
    function update($table, $values, $filter) {
        $query = "UPDATE `".$table."` SET ";
        $i = 1;
        foreach ($values as $key => $value) {
            $query .= "`".$key."` = '".$value."'";
            if($i != count($values)) {
                $query .= ", ";
            }
            $i++;
        }
        $query .= $this->get_filter($filter).";";
        $this->query($query);
    }

    // Connect to database
    function connect() {
        try {
            $this->connection = new PDO($this->dsn, $this->user, $this->password);
            $this->connected = TRUE;
        } catch (PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            $this->connected = FALSE;
        }
    }

    // Constructor
    function __construct($dsn, $user, $password) {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
    }
}