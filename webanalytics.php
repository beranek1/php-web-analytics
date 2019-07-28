<?php
/*
#-----------------------------------------
| WebAnalytics
| https://webanalytics.one
#-----------------------------------------
| made by beranek1
| https://github.com/beranek1
#-----------------------------------------
*/

/*
# Settings
*/
$web_analytics_db = new web_db_manager("mysql:dbname=database;host=127.0.0.1", "user", "password");
$web_auto_run = TRUE;

include "websettings.php";

/*
# Source
*/

if($web_auto_run) {
// Connect to database
$web_analytics_db->connect();

// Runs WebAnalytics
$web_analytics = new web_analytics($web_analytics_db, $_SERVER, $_COOKIE);
}

/* Classes */

// WebAnalytics database manager
class web_db_manager {
    public $connected = false;
    private $connection = null;
    private $user = null;
    private $password = null;
    private $dsn = null;

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

    function count($table, $filter = null) {
        $result = $this->get_one_row("SELECT COUNT(*) FROM `".$table."`".$this->get_filter($filter).";");
        return $result[0];
    }

    function get_one_row($query) {
        foreach ($this->query($query) as $row) {
            return $row;
        }
        return null;
    }
    
    function first($table, $keys, $filter) {
        return $this->get_one_row("SELECT ".$keys." FROM ".$table."".$this->get_filter($filter)." LIMIT 1;");
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

    function delete($table, $filter) {
        $this->query("DELETE FROM ".$table."".$this->get_filter($filter).";");
    }

    function query($query) {
        return $this->connection->query($query);
    }

    function create_table($name, $keys) {
        $query = "CREATE TABLE IF NOT EXISTS `".$name."` (";
        foreach ($keys as $key => $value) {
            $query .= "`".$key."` ".$value.", ";
        }
        $query .= "`time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);";
        $this->query($query);
    }

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

    function connect() {
        try {
            $this->connection = new PDO($this->dsn, $this->user, $this->password);
            $this->connected = TRUE;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            $this->connected = FALSE;
        }
    }

    function __construct($dsn, $user, $password) {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
    } 
}

// WebAnalytics

class web_analytics {
    private $db_manager = null;
    private $s = null;
    private $h = null;
    private $d = null;
    private $profile_id = null;
    private $ua = null;
    private $c = null;
    private $u_country_code = null;
    private $u_ip = null;
    private $u_language = null;
    private $ubid = null;
    
    private $topleveltocountry = array(
        "gov" => "US",
        "edu" => "US",
        "com" => "US",
        "net" => "US",
        "bayern" => "DE"
    );
    
    function get_country_by_host($host, $topleveltocountry = null) {
        if(isset($host) && filter_var($host, FILTER_VALIDATE_IP) == false) {
            $domainparts = explode(".", $host);
            $topleveldomain = $domainparts[count($domainparts) - 1];
            if(strlen($topleveldomain) == 2) {
                return strtoupper($topleveldomain);
            } else if(isset($topleveltocountry) && array_key_exists($topleveldomain, $topleveltocountry)) {
                return strtoupper($topleveltocountry[$topleveldomain]);
            }
        }
        return null;
    }

    // Get user language and country from hostname and http header
    function get_country_code($host) {
        if(isset($this->s["HTTP_CF_IPCOUNTRY"])) {
            return $this->s["HTTP_CF_IPCOUNTRY"];
        }
        return get_country_by_host($host, $this->topleveltocountry);
    }
    
    // Anonymize ip address
    function anonymize_ip($ip) {
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipparts = explode(":", $ip);
            if(count($ipparts) == 8) {
                $ip = $ipparts[0].":".$ipparts[1].":".$ipparts[2]."::";
            } else {
                if($ipparts[2] == "") {
                    $ip = $ipparts[0].":".$ipparts[1]."::";
                } else if($ipparts[1] == "") {
                    $ip = $ipparts[0]."::";
                } else {
                    $ip = $ipparts[0].":".$ipparts[1].":".$ipparts[2]."::";
                }
            }
        } else if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipparts = explode(".", $ip);
            if(count($ipparts) == 4) {
                $ip = $ipparts[0].".".$ipparts[1].".".$ipparts[2].".0";
            }
        }
        return $ip;
    }

    function check_database() {
        $this->db_manager->create_table("wa_ips", array(
            "ip" => "VARCHAR(45) PRIMARY KEY",
            "host" => "VARCHAR(253)",
            "country" => "VARCHAR(2)",
            "isp" => "VARCHAR(127)",
            "last_update" => "TIMESTAMP NULL"
        ));
        $this->db_manager->create_table("wa_profiles", array(
            "id" => "VARCHAR(10) PRIMARY KEY",
            "screen_width" => "VARCHAR(9)",
            "screen_height" => "VARCHAR(9)",
            "interface_width" => "VARCHAR(9)",
            "interface_height" => "VARCHAR(9)",
            "color_depth" => "VARCHAR(7)",
            "pixel_depth" => "VARCHAR(7)",
            "cookies_enabled" => "VARCHAR(5)",
            "java_enabled" => "VARCHAR(5)"
        ));
        $this->db_manager->create_table("wa_trackers", array(
            "id" => "VARCHAR(20) PRIMARY KEY",
            "domain" => "TEXT",
            "browser_id" => "VARCHAR(15) NOT NULL",
            "user_agent" => "TEXT"
        ));
        $this->db_manager->create_table("wa_browsers", array(
            "id" => "VARCHAR(15) PRIMARY KEY",
            "ip" => "VARCHAR(45) NOT NULL",
            "country" => "VARCHAR(2)",
            "language" => "VARCHAR(2)",
            "user_agent" => "TEXT",
            "profile_id" => "VARCHAR(10)",
            "last_update" => "TIMESTAMP NULL"
        ));
        $this->db_manager->create_table("wa_requests", array(
            "id" => "VARCHAR(15) PRIMARY KEY",
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
            "browser_id" => "VARCHAR(15)",
        ));
    }
    
    // Get ISP's unique id
    function get_isp($host) {
        if(isset($host) && filter_var($host, FILTER_VALIDATE_IP) == false) {
            $domain_parts = explode(".", $host);
            if(count($domain_parts) >= 2) {
                return $domainparts[count($domainparts) - 2] . "." . $domainparts[count($domainparts) - 1];
            }
        }
        return null;
    }
    
    // Get network's unique id
    function save_ip($ip, $anonymize = FALSE) {
        if(!isset($ip)) {
            return null;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $host = gethostbyaddr($ip);
        }
        $isp = get_isp($host);
        $this->u_country_code = get_country_code($host);
        if($anonymize) {
            $ip = $this->anonymize_ip($ip);
            $host = null;
        }
        $this->db_manager->add("wa_ips", array(
            "ip" => $ip,
            "host" => $host,
            "country" => $this->u_country_code,
            "isp" => $isp
        ));
        return $ip;
    }
    
    // Use cookies set by tracking script to get device's unique profile id
    function get_profile() {
        if(!isset($this->c["device_profile"]) && !isset($this->c["browser_profile"])) {
            return null;
        }
        $c_profile = array_merge(json_decode($this->c["device_profile"], true), json_decode($this->c["browser_profile"], true));
        $search_keys = array("screen_width", "screen_height", "interface_width", "interface_height", "color_depth", "pixel_depth", "cookies_enabled", "java_enabled");
        $search_query = "";
        $search_count = 0;
        $profile = array("id" => $this->db_manager->generate_id());
        foreach ($search_keys as $key) {
            if($search_count != 0) {
                $search_query .= " AND ";
            }
            if(isset($c_profile[$key]) && $c_profile[$key] != null) {
                $profile[$key] = $c_profile[$key];
                $search_query .= "".$key." = '".strval($profile[$key])."'";
            } else {
                $search_query .= "".$key." IS NULL";
            }
            $search_count++;
        }
        $row = $this->db_manager->get_one_row("SELECT id FROM wa_profiles WHERE ".$search_query." LIMIT 1;");
        if($row != null) {
            return $row["id"];
        }
        $this->db_manager->add("wa_profiles", $profile);
        return $profile["id"];
    }
    
    // Identify the user and update information
    function indentify_browser() {
        if(isset($this->c["webid"]) && strlen($this->c["webid"]) == 20) {
            $row = $this->db_manager->first("wa_trackers", "browser_id", array("id" => $this->c["webid"], "domain" => $this->d));
            if($row != null) {
                $this->db_manager->update("wa_trackers", array("time" => date('Y-m-d H:i:s')), array("id" => $this->c["webid"]));
                if($this->db_manager->first("wa_browsers", "id", array("id" => $row["browser_id"])) != null) {
                    setcookie("webid", $this->c["webid"], time()+60*60*24*180, "/", $this->d);
                    $this->db_manager->update("wa_browsers", array(
                        "ip" => $this->u_ip,
                        "profile_id" => $this->profile_id,
                        "user_agent" => $this->ua,
                        "last_update" => date('Y-m-d H:i:s')
                    ), array("id" => $row["browser_id"]));
                    return $row["browser_id"];
                }
            }
        }
        $cid = $this->db_manager->generate_id(20);
        $result = null;
        if($this->u_language != null) {
            $result = $this->db_manager->query("SELECT id FROM wa_browsers WHERE ip = '".$this->u_ip."' AND user_agent LIKE '".$this->ua."' AND language = '".$this->u_language."' AND last_update >= '".date('Y-m-d H:i:s', strtotime("-48 hours"))."';");
        } else {
            $result = $this->db_manager->query("SELECT id FROM wa_browsers WHERE ip = '".$this->u_ip."' AND user_agent LIKE '".$this->ua."' AND language IS NULL AND last_update >= '".date('Y-m-d H:i:s', strtotime("-48 hours"))."';");
        }
        $ubid = "";
        $ubid_count = 0;
        foreach ($result as $row) {
            $ubid = $row["id"];
            $ubid_count++;
        }
        if($ubid_count == 1) {
            $this->db_manager->update("wa_browsers", array("last_update" => date('Y-m-d H:i:s')), array("id" => $ubid));
            $cidrow = $this->db_manager->get_one_row("SELECT id, domain, time FROM wa_trackers".$this->db_manager->get_filter(array("browser_id" => $ubid, "user_agent" => $this->ua))." ORDER BY time DESC LIMIT 1;");
            if($cidrow != null) {
                if(strtotime($cidrow["time"]) >= strtotime("-90 days") && $cidrow["domain"] == $this->d) {
                    setcookie("webid", $cidrow["id"], time()+60*60*24*180, "/", $this->d);
                    $this->db_manager->update("wa_trackers", array("time" => date('Y-m-d H:i:s')), array("id" => $cidrow["id"]));
                    return $ubid;
                }
            }
            $this->db_manager->delete("wa_trackers", array("browser_id" => $ubid, "user_agent" => $this->ua, "domain" => $this->d));
            $this->db_manager->add("wa_trackers", array(
                "id" => $cid,
                "domain" => $this->d,
                "browser_id" => $ubid,
                "user_agent" => $this->ua
            ));
            setcookie("webid", $cid, time()+60*60*24*180, "/", $this->d);
            return $ubid;
        }
        $ubid = $this->db_manager->generate_id(15);
        $this->db_manager->add("wa_trackers", array(
            "id" => $cid,
            "domain" => $this->d,
            "browser_id" => $ubid,
            "user_agent" => $this->ua
        ));
        setcookie("webid", $cid, time()+60*60*24*180, "/", $this->d);
        $this->db_manager->add("wa_browsers", array(
            "id" => $ubid,
            "ip" => $this->u_ip,
            "country" => $this->u_country_code,
            "language" => $this->u_language,
            "user_agent" => $this->ua,
            "profile_id" => $this->profile_id
        ));
        return $ubid;
    }
    
    // Get information about the request and add it to the database
    function save_request() {
        $this->db_manager->add("wa_requests", array(
            "id" => $this->db_manager->generate_id(15),
            "accept" => isset($this->d['HTTP_ACCEPT']) ? "".explode(",", $this->s['HTTP_ACCEPT'])[0]."" : null,
            "protocol" => isset($this->s['REQUEST_SCHEME']) ? $this->s["REQUEST_SCHEME"] : null,
            "port" => isset($this->s["SERVER_PORT"]) ? $this->s['SERVER_PORT'] : null,
            "host" => strtolower($this->h),
            "uri" => isset($this->s["REQUEST_URI"]) ? $this->s["REQUEST_URI"] : null,
            "referrer" => isset($this->s["HTTP_REFERER"]) ? $this->s["HTTP_REFERER"] : null,
            "visitor_ip" => $this->u_ip,
            "visitor_country" => $this->u_country_code,
            "cf_ray_id" => isset($this->s["HTTP_CF_RAY"]) ? $this->s["HTTP_CF_RAY"] : null,
            "user_agent" => $this->ua,
            "browser_id" => $this->ubid
        ));
    }
    
    // Construct: web_analytics({db_manager}, $_SERVER, $_COOKIE)
    // If you don't want to anonymize ip adresses: web_analytics({db_manager}, $_SERVER, $_COOKIE, FALSE)
    // Please remember to write a privacy policy especially if you don't anonymize ip adresses and live in the EU.
    function __construct($db_manager, $server, $cookies, $anonymizeips = TRUE) {
        if($db_manager->connected) {
            $this->db_manager = $db_manager;
            $this->s = $server;
            $this->ua = isset($this->s['HTTP_USER_AGENT']) ? strtolower($this->s['HTTP_USER_AGENT']) : null;
            $this->c = $cookies;
            if(isset($this->s["HTTP_HOST"])) {
                $this->h = $this->s["HTTP_HOST"];
                $domain = strtolower($this->h);
                if(filter_var($domain, FILTER_VALIDATE_IP) == false) {
                    $domain_parts = explode(".", $domain);
                    $this->d = $domain_parts[count($domain_parts) - 2] . "." . $domain_parts[count($domain_parts) - 1];
                } else { $this->d = $domain; }
            }
            $this->u_language = isset($this->s["HTTP_ACCEPT_LANGUAGE"]) ? substr($this->s['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $this->check_database();
            $this->u_ip = $this->save_ip($this->s['REMOTE_ADDR'], $anonymizeips);
            $this->profile_id = $this->get_profile();
            $this->ubid = $this->indentify_browser();
            $this->save_request();
        } else {
            error_log("WebAnalytics unable to connect to database\n");
        }
    }
}