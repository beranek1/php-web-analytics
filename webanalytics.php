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
$web_analytics_db = new web_db_manager("user", "password", "database", "localhost");
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

// Closes database connection
$web_analytics_db->close();
}

/* Classes */

// WebAnalytics database manager
class web_db_manager {
    public $connected = false;
    private $connection = null;
    private $user = null;
    private $password = null;
    private $database = null;
    private $host = null;
    private $type = null;

    function get_filter($filter) {
        if($filter == null) {
            return "";
        }
        $query = " WHERE ";
        $i = 1;
        foreach ($filter as $key => $value) {
            if(isset($value)) {
                $query .= "`".$key."` = '".$value."'";
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
        $count = 0;
        $query = "SELECT COUNT(*) FROM `".$table."`".$this->get_filter($filter).";";
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
                $values .= "'".$value."'";
                $i++;
            }
        }
        $query = "INSERT INTO ".$table." (".$keys.") VALUES (".$values.");";
        if(!$this->connection->query("INSERT INTO ".$table." (".$keys.") VALUES (".$values.");")) {
            error_log("".$this->connection->error."\n");
        }
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
        if(!$this->query($query)) {
            error_log("".$this->connection->error."\n");
        }
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
        if(!$this->query($query)) {
            error_log("".$this->connection->error."\n");
        }
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
    private $u_profile = null;
    
    // Check whether a string starts with a specific word
    function startsWith($haystack, $needle) {
        return $needle == "" || strrpos($haystack, $needle, -strlen($haystack)) != false;
    }
    
    // Check whether a string ends with a specific word
    function endsWith($haystack, $needle) {
        return $needle == "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) != false);
    }
    
    // Check user agent and hostname to find out whether the visitor is a bot
    function check_if_bot() {
        if((preg_match('/googlebot/i', $this->ua) && ($this->endsWith($this->u_host, ".googlebot.com") || $this->endsWith($this->u_host, ".google.com"))) ||
            (preg_match('/bingbot/i', $this->ua) && $this->endsWith($this->u_host, ".search.msn.com")) ||
            (preg_match('/yandexbot/i', $this->ua) || preg_match('/yandeximages/i', $this->ua)) && $this->endsWith($this->u_host, ".yandex.com") ||
            (preg_match('/baiduspider/i', $this->ua) && $this->endsWith($this->u_host, ".crawl.baidu.com")) ||
            ((preg_match('/duckduckbot/i', $this->ua) || preg_match('/duckduckgo/i', $this->ua)) && $this->startsWith($this->u_host, "72.94.249.")) ||
            (preg_match('/archive.org_bot/i', $this->ua) && $this->endsWith($this->u_host, ".archive.org"))) {
            $this->u_bot = 1;
        }
    }
    function get_bot() {
        $bot_array = array('/googlebot/i' => 'Google',
                        '/bingbot/i' => 'Bing',
                        '/twitterbot/i' => 'Twitter',
                        '/baiduspider/i' => 'Baidu',
                        '/yandexbot/i' => 'Yandex',
                        '/yandeximages/i' => 'Yandex',
                        '/duckduckbot/i' => 'DuckDuckGo',
                        '/duckduckgo/i' => 'DuckDuckGo',
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
                        '/nintendo 3ds/i' => 'Nintendo 3DS',
                        '/nintendo wii/i' => 'Nintendo Wii',
                        '/nintendo wiiu/i' => 'Nintendo WiiU',
                        '/playstation/i' => 'Playstation',
                        '/playstation 4/i' => 'Playstation 4',
                        '/xbox/i' => 'Xbox'
                        );
        foreach ($os_array as $regex => $value) { 
            if (preg_match($regex, $this->ua)) {
                return $value;
            }
        }
        return null;
    }
    
    // Get the type of device from the user agent
    function get_device() {
        $device_array = array('/windows nt/i' => 'PC',
                        '/windows xp/i' => 'PC',
                        '/windows me/i' => 'PC',
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
                        '/nintendo 3ds/i' => 'Nintendo 3DS',
                        '/new nintendo 3ds/i' => 'New Nintendo 3DS',
                        '/nintendo wii/i' => 'Nintendo Wii',
                        '/nintendo wiiu/i' => 'Nintendo WiiU',
                        '/playstation/i' => 'Playstation',
                        '/playstation 4/i' => 'Playstation 4',
                        '/xbox/i' => 'Xbox'
                        );
        foreach ($device_array as $regex => $value) { 
            if (preg_match($regex, $this->ua)) {
                return $value;
            }
        }
        return null;
    }
    
    // Get the browser name from the user agent
    function get_browser() {
        $browser_array = array(
            '/mozilla/i' => 'Mozilla Compatible Agent',
            '/applewebkit/i' => 'AppleWebKit Agent',
            '/mobile/i' => 'Handheld Browser',
            '/ ie/i' => 'Internet Explorer',
            '/msie/i' => 'Internet Explorer',
            '/safari/i' => 'Safari',
            '/chrome/i' => 'Chrome',
            '/gsa/i' => 'Google App',
            '/firefox/i' => 'Firefox',
            '/opera/i' => 'Opera',
            '/opr/i' => 'Opera',
            '/edge/i' => 'Edge',
            '/yabrowser/i' => 'Yandex Browser',
            '/baidubrowser/i' => 'Baidu Browser',
            '/comodo_dragon/i' => 'Comodo Dragon',
            '/netscape/i'=> 'Netscape',
            '/navigator/i'=> 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/ubrowser/i' => 'UC Browser',
            '/amazonwebappplatform/i' => 'Amazon Silk',
            '/silk-accelerated=true/i' => 'Amazon Silk',
            '/silk/i' => 'Amazon Silk',
            '/iemobile/i' => 'Internet Explorer',
            '/nintendo 3ds/i' => '3DS Browser',
            '/nintendobrowser/i' => 'Nintendo Browser',
            '/playstation 4/i' => 'PS4 Browser',
            '/dalvik/i' => 'Android Application',
            '/curl/i' => 'cUrl Application',
            '/zend_http_client/i' => "Zend_Http_Client"
        );
        foreach ($browser_array as $regex => $value) { 
            if (preg_match($regex, $this->ua)) {
                return $value;
            }
        }
        return null;
    }
    
    // Check whether the visitor uses a mobile device using the user agent
    function check_if_mobile() {
        if (preg_match('/mobile/i', $this->ua)) {
            $this->u_mobile = 1;
        }
    }
    
    // Get user language and country from hostname and http header
    function get_location() {
        if(filter_var($this->u_host, FILTER_VALIDATE_IP) == false) {
            $domainparts = explode(".", $this->u_host);
            $domainend = $domainparts[count($domainparts) - 1];
            if(strlen($domainend) == 2) {
                $this->u_country_code = strtoupper($domainend);
            } elseif ($domainend == "com" || $domainend == "net" || $domainend == "org") {
                $this->u_country_code = "US";
            } elseif(isset($this->s["HTTP_CF_IPCOUNTRY"])) {
                $this->u_country_code = $this->s["HTTP_CF_IPCOUNTRY"];
            }
        }
        $this->u_language = isset($this->s["HTTP_ACCEPT_LANGUAGE"]) ? substr($this->s['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
    }
    
    // Anonymize ip address
    function anonymize_ip() {
        $prefix = "ipv4";
        if(filter_var($this->u_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $prefix = "ipv6";
        }
        $this->u_ip = $prefix.".".md5($this->u_ip);
    }
    
    // Get ISP's unique id
    function get_isp() {
        $this->db_manager->create_table("isps", array(
            "id" => "VARCHAR(10) PRIMARY KEY",
            "domain" => "VARCHAR(127) NOT NULL",
            "name" => "TEXT",
            "country" => "VARCHAR(2)",
            "last_update" => "TIMESTAMP NULL"
        ));
        if(isset($this->u_host) && filter_var($this->u_host, FILTER_VALIDATE_IP) == false) {
            $domain_parts = explode(".", $this->u_host);
            if(count($domain_parts) >= 2) {
                $domain = $domainparts[count($domainparts) - 2] . "." . $domainparts[count($domainparts) - 1];
                $nrow = $this->db_manager->get_one_row("SELECT id FROM isps WHERE domain = '".$domain."' LIMIT 1;");
                if($nrow != null) {
                    $this->isp_id = $nrow[0];
                } else {
                    $this->isp_id = $this->db_manager->generate_id();
                    $this->db_manager->add("isps", array(
                        "id" => $this->isp_id,
                        "domain" => $domain,
                        "country" => $this->u_country_code
                    ));
                }
            }
        }
    }
    
    // Get network's unique id
    function get_network() {
        $this->db_manager->create_table("networks", array(
            "id" => "VARCHAR(15) PRIMARY KEY",
            "ip" => "VARCHAR(45) NOT NULL",
            "host" => "VARCHAR(253)",
            "country" => "VARCHAR(2)",
            "isp_id" => "VARCHAR(10)",
            "last_update" => "TIMESTAMP NULL"
        ));
        if(isset($this->u_ip)) {
            $nrow = $this->db_manager->get_one_row("SELECT id, host FROM networks WHERE ip = '".$this->u_ip."' LIMIT 1;");
            if($nrow != null) {
                $this->unid = $nrow[0];
                $this->db_manager->update("networks", array("host" => $this->u_host), array("id" => $this->unid));
            } else {
                $this->unid = $this->db_manager->generate_id(15);
                $this->db_manager->add("networks", array(
                    "id" => $this->unid,
                    "ip" => $this->u_ip,
                    "host" => $this->u_host,
                    "country" => $this->u_country_code,
                    "isp_id" => $this->isp_id
                ));
            }
        }
    }
    
    // Get agent's unique id
    function get_agent() {
        $this->db_manager->create_table("agents", array(
            "id" => "VARCHAR(10) PRIMARY KEY",
            "agent" => "TEXT",
            "browser" => "VARCHAR(40)",
            "os" => "VARCHAR(40)",
            "device" => "VARCHAR(40)",
            "mobile" => "TINYINT(1)",
            "bot" => "TINYINT(1)",
            "bot_name" => "VARCHAR(30)"
        ));
        if($this->ua != null && $this->ua != "") {
            $aidrow = $this->db_manager->get_one_row("SELECT id FROM agents WHERE agent LIKE '".$this->ua."' LIMIT 1;");
            if($aidrow != null) {
                $this->agent_id = $aidrow[0];
            } else {
                $this->agent_id = $this->db_manager->generate_id();
                $this->db_manager->add("agents", array(
                    "id" => $this->agent_id,
                    "agent" => $this->ua,
                    "browser" => $this->get_browser(),
                    "os" => $this->get_os(),
                    "device" => $this->get_device(),
                    "mobile" => $this->u_mobile,
                    "bot" => $this->u_bot,
                    "bot_name" => $this->get_bot()
                ));
            }
        }
    }
    
    // Use cookies set by tracking script to get device's unique profile id
    function get_profile() {
        $this->db_manager->create_table("profiles", array(
            "id" => "VARCHAR(10) PRIMARY KEY",
            "screen_width" => "INT(9)",
            "screen_height" => "VARCHAR(9)",
            "interface_width" => "INT(9)",
            "interface_height" => "INT(9)",
            "color_depth" => "INT(7)",
            "pixel_depth" => "INT(7)",
            "cookies_enabled" => "TINYINT(1)",
            "java_enabled" => "TINYINT(1)"
        ));
        if(isset($this->c["device_profile"]) && isset($this->c["browser_profile"])) {
            $profile = array();
            $profile["id"] = $this->db_manager->generate_id();
            $device_profile = json_decode($this->c["device_profile"], true);
            $profile["screen_width"] = isset($device_profile["screen_width"]) ? intval($device_profile["screen_width"]) : null;
            $profile["screen_height"] = isset($device_profile["screen_height"]) ? intval($device_profile["screen_height"]) : null;
            $profile["interface_width"] = isset($device_profile["interface_width"]) ? intval($device_profile["interface_width"]) : null;
            $profile["interface_height"] = isset($device_profile["interface_height"]) ? intval($device_profile["interface_height"]) : null;
            $profile["color_depth"] = isset($device_profile["color_depth"]) ? intval($device_profile["color_depth"]) : null;
            $profile["pixel_depth"] = isset($device_profile["pixel_depth"]) ? intval($device_profile["pixel_depth"]) : null;
            setcookie("device_profile", json_encode($device_profile), time()+60*60*24*90, "/", $this->d);
            $browser_profile = json_decode($this->c["browser_profile"], true);
            if(isset($browser_profile["cookies_enabled"]) && isset($browser_profile["java_enabled"])) {
                if(is_int($browser_profile["cookie_enabled"]) && is_int($browser_profile["java_enabled"])) {
                    $profile["cookies_enabled"] = intval($browser_profile["cookies_enabled"]) == 1 ? 1 : 0;
                    $profile["java_enabled"] = intval($browser_profile["java_enabled"]) == 1 ? 1 : 0;
                } else if(is_string($browser_profile["cookie_enabled"]) && is_string($browser_profile["java_enabled"])) {
                    $profile["cookies_enabled"] = $browser_profile["cookies_enabled"] == "true" ? 1 : 0;
                    $profile["java_enabled"] = $browser_profile["java_enabled"] == "true" ? 1 : 0;
                } else if(is_bool($browser_profile["cookies_enabled"]) && is_bool($browser_profile["java_enabled"])) {
                    $profile["cookies_enabled"] = $browser_profile["cookies_enabled"] == true ? 1 : 0;
                    $profile["java_enabled"] = $browser_profile["java_enabled"] == true ? 1 : 0;
                }
            }
            setcookie("browser_profile", json_encode($browser_profile), time()+60*60*24*90, "/", $this->d);
            $search_keys = array("screen_width", "screen_height", "interface_width", "interface_height", "color_depth", "pixel_depth", "cookies_enabled", "java_enabled");
            $search_query = "";
            $search_count = 0;
            foreach ($search_keys as $key) {
                if(isset($profile[$key]) && $profile[$key] != null) {
                    if($search_count == 0) {
                        $search_query .= "".$key." = '".$profile[$key]."'";
                    } else {
                        $search_query .= " AND ".$key." = '".$profile[$key]."'";
                    }
                } else {
                    if($search_count == 0) {
                        $search_query .= "".$key." IS NULL";
                    } else {
                        $search_query .= " AND ".$key." IS NULL";
                    }
                }
                $search_count++;
            }
            $row = $this->db_manager->get_one_row("SELECT id FROM profiles WHERE ".$search_query." LIMIT 1;");
            if($row != null) {
                $this->profile_id = $row[0];
            } else {
                $this->profile_id = $profile["id"];
                $this->db_manager->add("profiles", $profile);
            }
        }
    }
    
    // Identify the user and update information
    function indentify_browser() {
        $this->db_manager->create_table("trackers", array(
            "id" => "VARCHAR(20) PRIMARY KEY",
            "domain" => "TEXT",
            "browser_id" => "VARCHAR(15) NOT NULL",
            "agent_id" => "VARCHAR(10)"
        ));
        $this->db_manager->create_table("browsers", array(
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
        if(isset($this->unid)) {
            if(isset($this->c["webid"])) {
                $cookie_cid = $this->c["webid"];
                if(strlen($cookie_cid) == 20) {
                    $cidrow = $this->db_manager->get_one_row("SELECT browser_id FROM trackers WHERE id = '".$cookie_cid."' AND domain = '".$this->d."' LIMIT 1;");
                    if($cidrow != null) {
                        $this->db_manager->update("trackers", array("time" => date('Y-m-d H:i:s')), array("id" => $cookie_cid));
                        $row = $this->db_manager->get_one_row("SELECT id FROM browsers WHERE id = '".$cidrow[0]."' LIMIT 1;");
                        if($row != null) {
                            setcookie("webid", $cookie_cid, time()+60*60*24*180, "/", $this->d);
                            $this->ubid = $cidrow[0];
                            $this->db_manager->update("browsers", array(
                                "ip" => $this->u_ip,
                                "network_id" => $this->unid,
                                "profile_id" => $this->profile_id,
                                "agent_id" => $this->agent_id,
                                "last_update" => date('Y-m-d H:i:s')
                            ), array("id" => $this->ubid));
                            return;
                        }
                    }
                }
            }
            $cid = $this->db_manager->generate_id(20);
            $result = null;
            if($this->u_language != null) {
                $result = $this->db_manager->query("SELECT id FROM browsers WHERE network_id = '".$this->unid."' AND agent_id = '".$this->agent_id."' AND language = '".$this->u_language."' AND last_update >= '".date('Y-m-d H:i:s', strtotime("-48 hours"))."';");
            } else {
                if($this->u_bot == 1) {
                    $result = $this->db_manager->query("SELECT id FROM browsers WHERE network_id = '".$this->unid."' AND agent_id = '".$this->agent_id."' AND language IS NULL AND bot = 1 AND last_update >= '".date('Y-m-d H:i:s', strtotime("-90 days"))."';");
                } else {
                    $result = $this->db_manager->query("SELECT id FROM browsers WHERE network_id = '".$this->unid."' AND agent_id = '".$this->agent_id."' AND language IS NULL AND last_update >= '".date('Y-m-d H:i:s', strtotime("-48 hours"))."';");
                }
            }
            $data_ubid = "";
            $ubid_count = 0;
            if($result instanceof mysqli_result) {
                while($row = $result->fetch_row()) {
                    $data_ubid = $row[0];
                    $ubid_count++;
                }
                $result->close();
            }
            if($ubid_count == 1) {
                $this->ubid = $data_ubid;
                $this->db_manager->update("browsers", array("last_update" => date('Y-m-d H:i:s')), array("id" => $this->ubid));
                $cidrow = $this->db_manager->get_one_row("SELECT id, domain, time FROM trackers".$this->db_manager->get_filter(array("browser_id" => $data_ubid, "agent_id" => $this->agent_id))." ORDER BY time DESC LIMIT 1;");
                if($cidrow != null) {
                    if(strtotime($cidrow[2]) >= strtotime("-90 days") && $cidrow[1] == $this->d) {
                        setcookie("webid", $cidrow[0], time()+60*60*24*180, "/", $this->d);
                        $this->db_manager->update("trackers", array("time" => date('Y-m-d H:i:s')), array("id" => $cidrow[0]));
                        return;
                    }
                }
                $this->db_manager->query("DELETE FROM trackers WHERE browser_id = '".$this->ubid."' AND agent_id = '".$this->agent_id."' AND domain = '".$this->d."';");
                $this->db_manager->add("trackers", array(
                    "id" => $cid,
                    "domain" => $this->d,
                    "browser_id" => $this->ubid,
                    "agent_id" => $this->agent_id
                ));
                setcookie("webid", $cid, time()+60*60*24*180, "/", $this->d);
            } else {
                $this->ubid = $this->db_manager->generate_id(15);
                $this->db_manager->add("trackers", array(
                    "id" => $cid,
                    "domain" => $this->d,
                    "browser_id" => $this->ubid,
                    "agent_id" => $this->agent_id
                ));
                setcookie("webid", $cid, time()+60*60*24*180, "/", $this->d);
                $this->db_manager->add("browsers", array(
                    "id" => $this->ubid,
                    "ip" => $this->u_ip,
                    "country" => $this->u_country_code,
                    "language" => $this->u_language,
                    "mobile" => $this->u_mobile,
                    "bot" => $this->u_bot,
                    "agent_id" => $this->agent_id,
                    "network_id" => $this->unid,
                    "profile_id" => $this->profile_id
                ));
            }
        }
    }
    
    // Get information about the request and add it to the database
    function save_request() {
        $this->db_manager->create_table("requests", array(
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
        $this->db_manager->add("requests", array(
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
    function __construct($db_manager, $server, $clientcookies, $anonymousips = TRUE) {
        if($db_manager->connected) {
            $this->db_manager = $db_manager;
            $this->s = $server;
            $this->ua = isset($this->s['HTTP_USER_AGENT']) ? strtolower($this->s['HTTP_USER_AGENT']) : null;
            $this->c = $clientcookies;
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
            $this->check_if_mobile();
            $this->get_location();
            $this->get_isp();
            $this->get_network();
            $this->check_if_bot();
            $this->get_profile();
            $this->get_agent();
            $this->indentify_browser();
            $this->save_request();
        } else {
            error_log("WebAnalytics unable to connect to database\n");
        }
    }
    
    // Write tracking script
    function echo_script() {
        echo 
        "<script>
            var d = new Date();
            d.setTime(d.getTime() + (180*24*60*60*1000));
            var expires = \"expires=\"+d.toUTCString();
            var device = {};
            device.screen_width = screen.width;
            device.screen_height = screen.height;
            device.interface_width = (screen.width - screen.availWidth);
            device.interface_height = (screen.height - screen.availHeight);
            device.color_depth = screen.colorDepth;
            device.pixel_depth = screen.pixelDepth;
            document.cookie = \"device_profile=\" + JSON.stringify(device) + \"; \" + expires + \"; path=/; domain=".$this->d."\";
            var browser = {};
            browser.interface_width = (window.outerWidth - window.innerWidth);
            browser.interface_height = (window.outerHeight - window.innerHeight);
            browser.cookies_enabled = navigator.cookieEnabled;
            browser.java_enabled = navigator.javaEnabled();
            document.cookie = \"browser_profile=\" + JSON.stringify(browser) + \"; \" + expires + \"; path=/; domain=".$this->d."\";
            if(navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var location = {};
                    location.latitude = position.coords.latitude;
                    location.longitude = position.coords.longitude;
                    location.altitude = position.coords.altitude;
                    document.cookie = \"geolocation=\" + JSON.stringify(location) + \"; \" + expires + \"; path=/; domain=".$this->d."\";
                });
            }
        </script>";
    }
}