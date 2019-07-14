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
    private $agent_id = null;
    private $profile_id = null;
    private $isp_id = null;
    private $ua = null;
    private $c = null;
    private $u_country_code = null;
    private $u_ip = null;
    private $u_host = null;
    private $u_language = null;
    private $ubid = null;
    private $unid = null;
    private $u_mobile = 0;
    private $u_bot = 0;
    
    function analyse_user_agent($user_agent) {
        $result = array("browser" => array("name" => null, "version" => null));
        $gecko = false;
        if(preg_match("/Mozilla\/\d[\d.]* \([A-Za-z0-9_. ;:]*\) Gecko\/\d+/i", $user_agent)) {
            $gecko = true;
        }
        $webkit = false;
        if(preg_match("/Mozilla\/\d.\d \([A-Za-z0-9_. ;:]*\) AppleWebKit\/\d[\d.]* \(KHTML, like Gecko\)/i", $user_agent)) {
            $webkit = true;
        }
        if(preg_match_all("/\w+\/\d[\d.]*/", $user_agent, $matches)) {
            $browser = preg_split("/\//",$matches[0][array_key_last($matches[0])]);
            if($webkit) {
                if(preg_match("/safari/i", $browser[0]) && preg_match("/chrome/i", $user_agent)) {
                    $browser = preg_split("/\//",$matches[0][2]);
                }
            }
            $result["browser"]["name"] = $browser[0];
            $result["browser"]["version"] = $browser[1];
        }
        return $result;
    }

    function get_bot() {
        $bot_array = array('/googlebot/i' => 'Google',
                        '/bingbot/i' => 'Bing',
                        '/twitterbot/i' => 'Twitter',
                        '/baiduspider/i' => 'Baidu',
                        '/yandex/i' => 'Yandex',
                        '/duckduck/i' => 'DuckDuckGo',
                        '/archive.org_bot/i' => 'Archive.org');
        foreach ($bot_array as $regex => $value) { 
            if (preg_match($regex, $this->ua)) {
                return $value;
            }
        }
        return null;
    }
    
    //  Get the os name and version from the user agent
    function get_os() {
        $os_array = array('/windows nt/i' => 'Windows',
                        '/windows nt 10/i' => 'Windows 10',
                        '/windows nt 6.3/i' => 'Windows 8.1',
                        '/windows nt 6.2/i' => 'Windows 8',
                        '/windows nt 6.1/i' => 'Windows 7',
                        '/windows nt 6.0/i' => 'Windows Vista',
                        '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
                        '/windows nt 5.1/i' => 'Windows XP',
                        '/windows xp/i' => 'Windows XP',
                        '/windows nt 5.0/i' => 'Windows 2000',
                        '/windows me/i' => 'Windows ME',
                        '/win98/i' => 'Windows 98',
                        '/win95/i' => 'Windows 95',
                        '/win16/i' => 'Windows 3.11',
                        '/macintosh|mac os x/i' => 'Mac OS X',
                        '/mac_powerpc/i' => 'Mac OS 9',
                        '/linux/i' => 'Linux',
                        '/ubuntu/i' => 'Ubuntu',
                        '/cros/i' => 'Chrome OS',
                        '/iphone/i' => 'iOS',
                        '/ipod/i' => 'iOS',
                        '/ipad/i' => 'iOS',
                        '/android/i' => 'Android',
                        '/blackberry/i' => 'BlackBerry',
                        '/windows phone/i' => 'Windows Phone',
                        '/windows phone 7/i' => 'Windows Phone 7',
                        '/windows phone 8/i' => 'Windows Phone 8',
                        '/windows phone 8.1/i' => 'Windows Phone 8.1',
                        '/windows phone 10.0/i' => 'Windows 10 Mobile',
                        '/webos/i' => 'webOS',
                        '/tizen/i' => 'Tizen',
                        '/symbos/i' => 'Symbian OS',
                        '/cordova-amazon-fireos/i' => 'Fire OS',
                        '/nintendo/i' => 'Nintendo',
                        '/playstation/i' => 'Playstation',
                        '/xbox/i' => 'Xbox'
                        );
        $os = null;
        foreach ($os_array as $regex => $value) { 
            if (preg_match($regex, $this->ua)) {
                $os = $value;
            }
        }
        return $os;
    }
    
    // Get the type of device from the user agent
    function get_device() {
        $device_array = array('/windows/i' => 'PC',
                        '/win98/i' => 'PC',
                        '/win95/i' => 'PC',
                        '/win16/i' => 'PC',
                        '/macintosh|mac os x/i' => 'Mac',
                        '/mac_powerpc/i' => 'Mac',
                        '/linux/i' => 'PC',
                        '/ubuntu/i' => 'PC',
                        '/cros/i' => 'Chromebook',
                        '/iphone/i' => 'iPhone',
                        '/ipod/i' => 'iPod',
                        '/ipad/i' => 'iPad',
                        '/android/i' => 'Android',
                        '/blackberry/i' => 'BlackBerry',
                        '/windows phone/i' => 'Windows Phone',
                        '/webos/i' => 'webOS Phone',
                        '/tizen/i' => 'Tizen Phone',
                        '/symbos/i' => 'Symbian Phone',
                        '/cordova-amazon-fireos/i' => 'Fire Device',
                        '/nintendo/i' => 'Nintendo Console',
                        '/playstation/i' => 'Playstation Console',
                        '/xbox/i' => 'Xbox Console'
                        );
        $device = null;
        foreach ($device_array as $regex => $value) { 
            if (preg_match($regex, $this->ua)) {
                $device = $value;
            }
        }
        return $device;
    }
    
    // Get user language and country from hostname and http header
    function get_country_code() {
        if(isset($this->s["HTTP_CF_IPCOUNTRY"])) {
            return $this->s["HTTP_CF_IPCOUNTRY"];
        }
        if(filter_var($this->u_host, FILTER_VALIDATE_IP) == false) {
            $domainparts = explode(".", $this->u_host);
            $domainend = $domainparts[count($domainparts) - 1];
            if(strlen($domainend) == 2) {
                return strtoupper($domainend);
            }
        }
        return null;
    }
    
    // Anonymize ip address
    function anonymize_ip() {
        $prefix = "ipv4";
        if(filter_var($this->u_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $prefix = "ipv6";
        }
        $this->u_ip = $prefix.".".md5($this->u_ip);
    }

    function check_database() {
        $this->db_manager->create_table("wa_isps", array(
            "id" => "VARCHAR(10) PRIMARY KEY",
            "domain" => "VARCHAR(127) NOT NULL",
            "name" => "TEXT",
            "country" => "VARCHAR(2)",
            "last_update" => "TIMESTAMP NULL"
        ));
        $this->db_manager->create_table("wa_networks", array(
            "id" => "VARCHAR(15) PRIMARY KEY",
            "ip" => "VARCHAR(45) NOT NULL",
            "host" => "VARCHAR(253)",
            "country" => "VARCHAR(2)",
            "isp_id" => "VARCHAR(10)",
            "last_update" => "TIMESTAMP NULL"
        ));
        $this->db_manager->create_table("wa_agents", array(
            "id" => "VARCHAR(10) PRIMARY KEY",
            "agent" => "TEXT",
            "browser" => "VARCHAR(40)",
            "browser_version" => "VARCHAR(10)",
            "os" => "VARCHAR(40)",
            "os_version" => "VARCHAR(10)",
            "device" => "VARCHAR(40)",
            "mobile" => "TINYINT(1)",
            "bot" => "TINYINT(1)",
            "bot_name" => "VARCHAR(30)"
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
            "agent_id" => "VARCHAR(10)"
        ));
        $this->db_manager->create_table("wa_browsers", array(
            "id" => "VARCHAR(15) PRIMARY KEY",
            "ip" => "VARCHAR(45) NOT NULL",
            "country" => "VARCHAR(2)",
            "language" => "VARCHAR(2)",
            "mobile" => "TINYINT(1)",
            "bot" => "TINYINT(1)",
            "agent_id" => "VARCHAR(10)",
            "network_id" => "VARCHAR(15) NOT NULL",
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
            "browser_id" => "VARCHAR(15)",
            "network_id" => "VARCHAR(15)"
        ));
    }
    
    // Get ISP's unique id
    function get_isp() {
        if(isset($this->u_host) && filter_var($this->u_host, FILTER_VALIDATE_IP) == false) {
            $domain_parts = explode(".", $this->u_host);
            if(count($domain_parts) >= 2) {
                $domain = $domainparts[count($domainparts) - 2] . "." . $domainparts[count($domainparts) - 1];
                $row = $this->db_manager->first("wa_isps", "id", array("domain" => $domain));
                if($row != null) {
                    return $row["id"];
                }
                $id = $this->db_manager->generate_id();
                $this->db_manager->add("wa_isps", array(
                    "id" => $id,
                    "domain" => $domain,
                    "country" => $this->u_country_code
                ));
                return $id;
            }
        }
        return null;
    }
    
    // Get network's unique id
    function get_network() {
        if(!isset($this->u_ip)) {
            return null;
        }
        $row = $this->db_manager->first("wa_networks", "id", array("ip" => $this->u_ip));
        if($row != null) {
            $this->db_manager->update("wa_networks", array("host" => $this->u_host), array("id" => $row[0]));
            return $row["id"];
        }
        $unid = $this->db_manager->generate_id(15);
        $this->db_manager->add("wa_networks", array(
            "id" => $unid,
            "ip" => $this->u_ip,
            "host" => $this->u_host,
            "country" => $this->u_country_code,
            "isp_id" => $this->isp_id
        ));
        return $unid;  
    }
    
    // Get agent's unique id
    function get_agent() {
        if($this->ua == null && $this->ua == "") {
            return null;
        }
        $row = $this->db_manager->get_one_row("SELECT id FROM wa_agents WHERE agent LIKE '".$this->ua."' LIMIT 1;");
        if($row != null) {
            return $row["id"];
        }
        $id = $this->db_manager->generate_id();
        $uaa = $this->analyse_user_agent($this->ua);
        $this->db_manager->add("wa_agents", array(
            "id" => $id,
            "agent" => $this->ua,
            "browser" => $uaa["browser"]["name"],
            "browser_version" => $uaa["browser"]["version"],
            "os" => $this->get_os(),
            "device" => $this->get_device(),
            "mobile" => $this->u_mobile,
            "bot" => $this->u_bot,
            "bot_name" => $this->get_bot()
        ));
        return $id;
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
                        "network_id" => $this->unid,
                        "profile_id" => $this->profile_id,
                        "agent_id" => $this->agent_id,
                        "last_update" => date('Y-m-d H:i:s')
                    ), array("id" => $row["browser_id"]));
                    return $row["browser_id"];
                }
            }
        }
        $cid = $this->db_manager->generate_id(20);
        $result = null;
        if($this->u_language != null) {
            $result = $this->db_manager->query("SELECT id FROM wa_browsers WHERE network_id = '".$this->unid."' AND agent_id = '".$this->agent_id."' AND language = '".$this->u_language."' AND last_update >= '".date('Y-m-d H:i:s', strtotime("-48 hours"))."';");
        } else {
            $result = $this->db_manager->query("SELECT id FROM wa_browsers WHERE network_id = '".$this->unid."' AND agent_id = '".$this->agent_id."' AND language IS NULL AND last_update >= '".date('Y-m-d H:i:s', strtotime("-48 hours"))."';");
        }
        $ubid = "";
        $ubid_count = 0;
        foreach ($result as $row) {
            $ubid = $row["id"];
            $ubid_count++;
        }
        if($ubid_count == 1) {
            $this->db_manager->update("wa_browsers", array("last_update" => date('Y-m-d H:i:s')), array("id" => $ubid));
            $cidrow = $this->db_manager->get_one_row("SELECT id, domain, time FROM wa_trackers".$this->db_manager->get_filter(array("browser_id" => $ubid, "agent_id" => $this->agent_id))." ORDER BY time DESC LIMIT 1;");
            if($cidrow != null) {
                if(strtotime($cidrow["time"]) >= strtotime("-90 days") && $cidrow["domain"] == $this->d) {
                    setcookie("webid", $cidrow["id"], time()+60*60*24*180, "/", $this->d);
                    $this->db_manager->update("wa_trackers", array("time" => date('Y-m-d H:i:s')), array("id" => $cidrow["id"]));
                    return $ubid;
                }
            }
            $this->db_manager->delete("wa_trackers", array("browser_id" => $ubid, "agent_id" => $this->agent_id, "domain" => $this->d));
            $this->db_manager->add("wa_trackers", array(
                "id" => $cid,
                "domain" => $this->d,
                "browser_id" => $ubid,
                "agent_id" => $this->agent_id
            ));
            setcookie("webid", $cid, time()+60*60*24*180, "/", $this->d);
            return $ubid;
        }
        $ubid = $this->db_manager->generate_id(15);
        $this->db_manager->add("wa_trackers", array(
            "id" => $cid,
            "domain" => $this->d,
            "browser_id" => $ubid,
            "agent_id" => $this->agent_id
        ));
        setcookie("webid", $cid, time()+60*60*24*180, "/", $this->d);
        $this->db_manager->add("wa_browsers", array(
            "id" => $ubid,
            "ip" => $this->u_ip,
            "country" => $this->u_country_code,
            "language" => $this->u_language,
            "mobile" => $this->u_mobile,
            "bot" => $this->u_bot,
            "agent_id" => $this->agent_id,
            "network_id" => $this->unid,
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
            "browser_id" => $this->ubid,
            "network_id" => $this->unid
        ));
    }
    
    // Construct: web_analytics({db_manager}, $_SERVER, $_COOKIE)
    // If you don't want to anonymize ip adresses: web_analytics({db_manager}, $_SERVER, $_COOKIE, FALSE)
    // Please remember to write a privacy policy especially if you don't anonymize ip adresses and live in the EU.
    function __construct($db_manager, $server, $cookies, $anonymousips = TRUE) {
        if($db_manager->connected) {
            $this->db_manager = $db_manager;
            $this->s = $server;
            $this->ua = isset($this->s['HTTP_USER_AGENT']) ? strtolower($this->s['HTTP_USER_AGENT']) : null;
            $this->c = $cookies;
            $this->u_ip = isset($this->s['REMOTE_ADDR']) ? $this->s['REMOTE_ADDR'] : null;
            if (filter_var($this->u_ip, FILTER_VALIDATE_IP)) {
                $this->u_host = gethostbyaddr($this->u_ip);
            }
            if($anonymousips && isset($this->s['REMOTE_ADDR'])) {
                $this->anonymize_ip();
            }
            if(isset($this->s["HTTP_HOST"])) {
                $this->h = $this->s["HTTP_HOST"];
                $domain = strtolower($this->h);
                if(filter_var($domain, FILTER_VALIDATE_IP) == false) {
                    $domain_parts = explode(".", $domain);
                    $this->d = $domain_parts[count($domain_parts) - 2] . "." . $domain_parts[count($domain_parts) - 1];
                } else { $this->d = $domain; }
            }
            $this->u_mobile = preg_match('/mobile/i', $this->ua) ? 1 : 0;
            $this->u_bot = $this->get_bot() != null ? 1 : 0;
            $this->u_language = isset($this->s["HTTP_ACCEPT_LANGUAGE"]) ? substr($this->s['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $this->u_country_code = $this->get_country_code();
            $this->check_database();
            $this->isp_id = $this->get_isp();
            $this->unid = $this->get_network();
            $this->agent_id = $this->get_agent();
            $this->profile_id = $this->get_profile();
            $this->ubid = $this->indentify_browser();
            $this->save_request();
        } else {
            error_log("WebAnalytics unable to connect to database\n");
        }
    }
}