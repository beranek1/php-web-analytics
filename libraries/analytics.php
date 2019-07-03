<?php
/*
#-----------------------------------------
| b1 web analytics
| https://beranek1.github.io/webanalytics/
#-----------------------------------------
| made by beranek1
| https://github.com/beranek1
#-----------------------------------------
*/

class b1_analytics {
    public $s = null;
    public $h = null;
    public $d = null;
    public $bot_name = null;
    public $agent_id = null;
    public $profile_id = null;
    public $isp_id = null;
    public $ua = null;
    public $c = null;
    public $u_os = null;
    public $u_browser = null;
    public $u_device = null;
    public $u_country_code = null;
    public $u_ip = null;
    public $u_host = null;
    public $u_location = null;
    public $u_latitude = null;
    public $u_longitude = null;
    public $u_language = null;
    public $ubid = null;
    public $unid = null;
    public $u_mobile = 0;
    public $u_bot = 0;
    public $u_profile = null;
    public $r_target = null;
    public $r_origin = null;
    public $r_protocol = null;
    public $r_port = null;
    public $r_rayid = null;
    public $rid = null;
    public $r_domain = null;
    public $r_accept = null;
    public $u_port = null;
    
    function get_row_count($query, $mysql) {
        $count = 0;
        $result = $mysql->query($query);
        if($result instanceof mysqli_result) {
            if($row = $result->fetch_row()) {
                $count = intval($row[0]);
            }
            $result->close();
        }
        return $count;
    }
    
    function get_rows_array($query, $mysql) {
        $rows = array();
        $result = $mysql->query($query);
        if($result instanceof mysqli_result) {
            while($row = $result->fetch_row()) {
                $rows[] = $row;
            }
            $result->close();
        }
        return $rows;
    }
    
    function get_one_row($query, $mysql) {
        $row0 = null;
        $result = $mysql->query($query);
        if($result instanceof mysqli_result) {
            if($row = $result->fetch_row()) {
                $row0 = $row;
            }
            $result->close();
        }
        return $row0;
    }
    
    function generateid($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= $characters[rand(0, $charactersLength - 1)];
        }
        return $id;
    }
    
    function generatequery($ary, $id, $field = "id") {
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
    
    function exgenquery($mysql, $table, $ary, $id, $field = "id") {
        $query = $this->generatequery($ary, $id, $field);
        if(!$mysql->query("INSERT INTO ".$table." (".$query["keys"].") VALUES (".$query["values"].");")) {
            error_log("".$mysql->error."\n");
        }
    }
    
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
                $this->bot_name = $value;
            }
        }
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
                $this->u_os = $value;
            }
        }
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
                $this->u_device = $value;
            }
        }
    }
    
    // Get the browser name from the user agents
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
                $this->u_browser = $value;
            }
        }
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
        $this->u_language = isset($this->s["HTTP_ACCEPT_LANGUAGE"]) ? substr($this->s['HTTP_ACCEPT_LANGUAGE'], 0, 2) : false;
    }
    
    // Anonymize ip address
    function anonymize_ip() {
        $prefix = "ipv4";
        if(filter_var($this->u_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $prefix = "ipv6";
        }
        $this->u_ip = $prefix + "." + md5($this->u_ip);
    }
    
    // Get ISP's unique id
    function get_isp($mysql) {
        $mysql->query("CREATE TABLE IF NOT EXISTS `isps` (id VARCHAR(10) PRIMARY KEY, domain VARCHAR(127) NOT NULL, name TEXT, country VARCHAR(2), last_update TIMESTAMP NULL, `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
        $domain = null;
        if(isset($this->u_host) && filter_var($this->u_host, FILTER_VALIDATE_IP) == false) {
            $domainparts = explode(".", $this->u_host);
            $domain = $domainparts[count($domainparts) - 2] . "." . $domainparts[count($domainparts) - 1];
        }
        if($domain != null) {
            $nrow = $this->get_one_row("SELECT id FROM isps WHERE domain = '".$domain."' LIMIT 1;", $mysql);
            if($nrow != null) {
                $this->isp_id = $nrow[0];
            } else {
                $this->isp_id = $this->generateid();
                $this->exgenquery($mysql, "isps", array("domain" => $domain, "country" => $this->u_country_code), $this->isp_id);
            }
        }
    }
    
    // Get network's unique id
    function get_network($mysql) {
        $mysql->query("CREATE TABLE IF NOT EXISTS `networks` (id VARCHAR(15) PRIMARY KEY, ip VARCHAR(45) NOT NULL, host VARCHAR(253), country VARCHAR(2), isp_id VARCHAR(10), last_update TIMESTAMP NULL, `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
        if(isset($this->u_ip)) {
            $nrow = $this->get_one_row("SELECT id, host FROM networks WHERE ip = '".$this->u_ip."' LIMIT 1;", $mysql);
            if($nrow != null) {
                $this->unid = $nrow[0];
                if($nrow[1] == null || $nrow[1] == "") {
                    $mysql->query("UPDATE networks SET host = '".$this->u_host."' WHERE id = '".$this->unid."';");
                }
            } else {
                $this->unid = $this->generateid(15);
                $this->exgenquery($mysql, "networks", array("ip" => $this->u_ip, "host" => $this->u_host, "country" => $this->u_country_code, "isp_id" => $this->isp_id), $this->unid);
            }
        }
    }
    
    // Get agent's unique id
    function get_agent($mysql) {
        $mysql->query("CREATE TABLE IF NOT EXISTS `agents` (id VARCHAR(10) PRIMARY KEY, agent TEXT, browser VARCHAR(40), os VARCHAR(40), device VARCHAR(40), mobile TINYINT(1), bot TINYINT(1), bot_name VARCHAR(30), `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
        if($this->ua != null && $this->ua != "") {
            $aidrow = $this->get_one_row("SELECT id, browser, os, device FROM agents WHERE agent LIKE '".$this->ua."' LIMIT 1;", $mysql);
            if($aidrow != null) {
                $this->agent_id = $aidrow[0];
                $this->u_browser = $aidrow[1];
                $this->u_os = $aidrow[2];
                $this->u_device = $aidrow[3];
            } else {
                $this->get_os();
                $this->get_device();
                $this->get_browser();
                $this->agent_id = $this->generateid();
                $this->exgenquery($mysql, "agents", array("agent" => $this->ua, "browser" => $this->u_browser, "os" => $this->u_os, "device" => $this->u_device, "mobile" => $this->u_mobile, "bot" => $this->u_bot, "bot_name" => $this->bot_name), $this->agent_id);
            }
        }
    }
    
    // Use cookies set by tracking script to get device's unique profile id
    function get_profile($mysql) {
        $mysql->query("CREATE TABLE IF NOT EXISTS `profiles` (id VARCHAR(10) PRIMARY KEY, screen_width INT(9), screen_height VARCHAR(9), interface_width INT(9), interface_height INT(9), color_depth INT(7), pixel_depth INT(7), cookies_enabled TINYINT(1), java_enabled TINYINT(1), `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
        if(isset($this->c["device_profile"]) && isset($this->c["browser_profile"])) {
            $this->u_profile = array();
            $device_profile = json_decode($this->c["device_profile"], true);
            if(isset($device_profile["screen_width"]) && isset($device_profile["screen_height"])) {
                $this->u_profile["screen_width"] = intval($device_profile["screen_width"]);
                $this->u_profile["screen_height"] = intval($device_profile["screen_height"]);
            }
            if(isset($device_profile["interface_width"]) && isset($device_profile["interface_height"])) {
                $this->u_profile["interface_width"] = intval($device_profile["interface_width"]);
                $this->u_profile["interface_height"] = intval($device_profile["interface_height"]);
            }
            if(isset($device_profile["color_depth"]) && isset($device_profile["pixel_depth"])) {
                $this->u_profile["color_depth"] = intval($device_profile["color_depth"]);
                $this->u_profile["pixel_depth"] = intval($device_profile["pixel_depth"]);
            }
            setcookie("device_profile", json_encode($device_profile), time()+60*60*24*90, "/", $this->d);
            $browser_profile = json_decode($this->c["browser_profile"], true);
            if(isset($browser_profile["cookies_enabled"]) && isset($browser_profile["java_enabled"])) {
                if(is_int($browser_profile["cookie_enabled"]) && is_int($browser_profile["java_enabled"])) {
                    $this->u_profile["cookies_enabled"] = intval($browser_profile["cookies_enabled"]) == 1 ? 1 : 0;
                    $this->u_profile["java_enabled"] = intval($browser_profile["java_enabled"]) == 1 ? 1 : 0;
                } else if(is_string($browser_profile["cookie_enabled"]) && is_string($browser_profile["java_enabled"])) {
                    $this->u_profile["cookies_enabled"] = $browser_profile["cookies_enabled"] == "true" ? 1 : 0;
                    $this->u_profile["java_enabled"] = $browser_profile["java_enabled"] == "true" ? 1 : 0;
                } else if(is_bool($browser_profile["cookies_enabled"]) && is_bool($browser_profile["java_enabled"])) {
                    $this->u_profile["cookies_enabled"] = $browser_profile["cookies_enabled"] == true ? 1 : 0;
                    $this->u_profile["java_enabled"] = $browser_profile["java_enabled"] == true ? 1 : 0;
                }
            }
            setcookie("browser_profile", json_encode($browser_profile), time()+60*60*24*90, "/", $this->d);
            $search_keys = array("screen_width", "screen_height", "interface_width", "interface_height", "color_depth", "pixel_depth", "cookies_enabled", "java_enabled");
            $search_query = "";
            $search_count = 0;
            foreach ($search_keys as $key) {
                if(isset($this->u_profile[$key]) && $this->u_profile[$key] != null) {
                    if($search_count == 0) {
                        $search_query .= "".$key." = '".$this->u_profile[$key]."'";
                    } else {
                        $search_query .= " AND ".$key." = '".$this->u_profile[$key]."'";
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
            $profile = $this->get_one_row("SELECT id FROM profiles WHERE ".$search_query." LIMIT 1;", $mysql);
            if($profile != null) {
                $this->profile_id = $profile[0];
            } else {
                $this->profile_id = $this->generateid();
                $this->exgenquery($mysql, "profiles", $this->u_profile, $this->profile_id);
            }
        }
    }
    
    // Identify the user and update information
    function indentify_browser($mysql) {
        $mysql->query("CREATE TABLE IF NOT EXISTS `trackers` (id VARCHAR(20) PRIMARY KEY, domain TEXT, browser_id VARCHAR(15) NOT NULL, agent_id VARCHAR(10), last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
        $mysql->query("CREATE TABLE IF NOT EXISTS `browsers` (id VARCHAR(15) PRIMARY KEY, ip VARCHAR(45), country VARCHAR(2), language VARCHAR(2), mobile TINYINT(1), bot TINYINT(1), agent_id VARCHAR(10), network_id VARCHAR(15) NOT NULL, profile_id VARCHAR(10), last_update TIMESTAMP NULL, `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
        if(isset($this->unid)) {
            $identified = false;
            if(isset($this->c["b1id"])) {
                $cookie_cid = $this->c["b1id"];
                if(strlen($cookie_cid) == 20) {
                    $cidrow = $this->get_one_row("SELECT browser_id FROM trackers WHERE id = '".$cookie_cid."' AND domain = '".$this->d."' LIMIT 1;", $mysql);
                    if($cidrow != null) {
                        $mysql->query("UPDATE trackers SET last_seen = '".date('Y-m-d H:i:s')."' WHERE id = '".$cookie_cid."';");
                        $row = $this->get_one_row("SELECT ip, network_id FROM browsers WHERE id = '".$cidrow[0]."' LIMIT 1;", $mysql);
                        if($row != null) {
                            setcookie("b1id", $cookie_cid, time()+60*60*24*180, "/", $this->d);
                            $this->ubid = $cidrow[0];
                            if($this->u_ip != $row[0] && $this->u_ip != null && $this->u_ip != "") {
                                $mysql->query("UPDATE browsers SET ip = '".$this->u_ip."' WHERE id = '".$this->ubid."';");
                            }
                            if($row[1] != null && $row[1] != "") {
                                if($this->unid != $row[1] && $this->unid != null && $this->unid != "") {
                                    $mysql->query("UPDATE browsers SET network_id = '".$this->unid."' WHERE id = '".$this->ubid."';");
                                }
                            } else {
                                $mysql->query("UPDATE browsers SET network_id = '".$this->unid."' WHERE id = '".$this->ubid."';");
                            }
                            if($this->profile_id != null) {
                                $mysql->query("UPDATE browsers SET profile_id = '".$this->profile_id."' WHERE id = '".$this->ubid."';");
                            }
                            $mysql->query("UPDATE browsers SET agent_id = '".$this->agent_id."', last_update = '".date('Y-m-d H:i:s')."' WHERE id = '".$this->ubid."';");
                            $identified = true;
                        }
                    }
                }
            }
            if($identified == false) {
                $cid = $this->generateid(20);
                $result = null;
                if($this->u_language != null) {
                    $result = $mysql->query("SELECT id FROM browsers WHERE network_id = '".$this->unid."' AND agent_id = '".$this->agent_id."' AND language = '".$this->u_language."' AND last_update >= '".date('Y-m-d H:i:s', strtotime("-48 hours"))."';");
                } else {
                    if($this->u_bot == 1) {
                        $result = $mysql->query("SELECT id FROM browsers WHERE network_id = '".$this->unid."' AND agent_id = '".$this->agent_id."' AND language IS NULL AND bot = 1 AND last_update >= '".date('Y-m-d H:i:s', strtotime("-90 days"))."';");
                    } else {
                        $result = $mysql->query("SELECT id FROM browsers WHERE network_id = '".$this->unid."' AND agent_id = '".$this->agent_id."' AND language IS NULL AND last_update >= '".date('Y-m-d H:i:s', strtotime("-48 hours"))."';");
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
                    $cidrow = null;
                    if($this->agent_id != null) {
                        $cidrow = get_one_row("SELECT id, domain, last FROM trackers WHERE browser_id = '".$data_ubid."' AND agent_id = '".$this->agent_id."' ORDER BY last_seen DESC LIMIT 1;", $mysql);
                    } else {
                        $cidrow = get_one_row("SELECT id, domain, last FROM trackers WHERE browser_id = '".$data_ubid."' AND agent_id IS NULL ORDER BY last_seen DESC LIMIT 1;", $mysql);
                    }
                    $cidregenerate = true;
                    if($cidrow != null) {
                        if(strtotime($cidrow[2]) >= strtotime("-90 days") && $cidrow[1] == $this->d) {
                            $cidregenerate = false;
                        }
                    }
                    if($cidregenerate == false) {
                        setcookie("b1id", $cidrow[0], time()+60*60*24*180, "/", $this->d);
                        $mysql->query("UPDATE trackers SET last_seen = '".date('Y-m-d H:i:s')."' WHERE id = '".$cidrow[0]."';");
                    } else {
                        $mysql->query("DELETE FROM trackers WHERE browser_id = '".$data_ubid."' AND agent_id = '".$this->agent_id."' AND domain = '".$this->d."';");
                        $this->exgenquery($mysql, "trackers", array("domain" => $this->d, "browser_id" => $data_ubid, "agent_id" => $this->agent_id), $cid);
                        setcookie("b1id", $cid, time()+60*60*24*180, "/", $this->d);
                    }
                    $this->ubid = $data_ubid;
                    $mysql->query("UPDATE browsers SET last_update = '".date('Y-m-d H:i:s')."' WHERE id = '".$this->ubid."';");
                } else {
                    $this->ubid = $this->generateid(15);
                    $this->exgenquery($mysql, "trackers", array("domain" => $this->d, "browser_id" => $this->ubid, "agent_id" => $this->agent_id), $cid);
                    setcookie("b1id", $cid, time()+60*60*24*180, "/", $this->d);
                    $this->exgenquery($mysql, "browsers", array("ip" => $this->u_ip, "country" => $this->u_country_code, "language" => $this->u_language, "mobile" => $this->u_mobile, "bot" => $this->u_bot, "agent_id" => $this->agent_id, "network_id" => $this->unid, "profile_id" => $this->profile_id), $this->ubid);
                }
            }
        }
    }
    
    // Get information about the request and add it to the database
    function save_request($mysql) {
        $mysql->query("CREATE TABLE IF NOT EXISTS `requests` (id VARCHAR(15) PRIMARY KEY, accept TEXT, protocol TEXT, port INT(6), host VARCHAR(253), uri TEXT, referrer TEXT, visitor_ip VARCHAR(45), visitor_country VARCHAR(2), cf_ray_id TEXT, browser_id VARCHAR(15), network_id VARCHAR(15), `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP);");
        $this->r_protocol = isset($this->s['REQUEST_SCHEME']) ? $this->s["REQUEST_SCHEME"] : null;
        $this->r_port = isset($this->s["SERVER_PORT"]) ? $this->s['SERVER_PORT'] : null;
        $this->r_rayid = isset($this->s["HTTP_CF_RAY"]) ? $this->s["HTTP_CF_RAY"] : null;
        if(isset($this->s["REQUEST_URI"])) {
            $uri = $this->s["REQUEST_URI"];
            if($uri != null && $uri != "" && $uri != "/") {
                $this->r_target = $uri;
            }
        }
        if(isset($this->s["HTTP_REFERER"])) {
            $origin = $this->s["HTTP_REFERER"];
            if($origin != null && $origin != "") {
                $this->r_origin = $origin;
            }
        }
        $this->r_accept = isset($this->d['HTTP_ACCEPT']) ? "".explode(",", $this->s['HTTP_ACCEPT'])[0]."" : null;
        $this->rid = $this->generateid(15);
        $this->r_domain = strtolower($this->h);
        $this->exgenquery($mysql, "requests", array("accept" => $this->r_accept, "protocol" => $this->r_protocol, "port" => $this->r_port, "host" => $this->r_domain, "uri" => $this->r_target, "referrer" => $this->r_origin, "visitor_ip" => $this->u_ip, "visitor_country" => $this->u_country_code, "cf_ray_id" => $this->r_rayid, "browser_id" => $this->ubid, "network_id" => $this->unid), $this->rid);
    }
    
    // Construct: b1_analytics({mysql}, $_SERVER, $_COOKIE)
    // If you don't want to anonymize ip adresses: b1_analytics({mysql}, $_SERVER, $_COOKIE, FALSE)
    // Please remember to write a privacy policy especially if you don't anonymize ip adresses and live in the EU.
    function __construct($mysql, $server, $clientcookies, $anonymousips = TRUE) {
        if(!$mysql->connect_errno) {
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
            $this->get_isp($mysql);
            $this->get_network($mysql);
            $this->check_if_bot();
            $this->get_profile($mysql);
            $this->get_agent($mysql);
            $this->indentify_browser($mysql);
            $this->save_request($mysql);
        } else {
            error_log("b1 web analytics unable to connect to database\n");
        }
    }
    
    // Write tracking script
    function echo_script() {
        echo 
        "<script>
            var b1d = new Date();
            b1d.setTime(trkd.getTime() + (180*24*60*60*1000));
            var b1expires = \"expires=\"+b1d.toUTCString();
            var b1device = {};
            b1device.screen_width = screen.width;
            b1device.screen_height = screen.height;
            b1device.interface_width = (screen.width - screen.availWidth);
            b1device.interface_height = (screen.height - screen.availHeight);
            b1device.color_depth = screen.colorDepth;
            b1device.pixel_depth = screen.pixelDepth;
            document.cookie = \"device_profile=\" + JSON.stringify(b1device) + \"; \" + b1expires + \"; path=/; domain=".$this->d."\";
            var b1browser = {};
            b1browser.interface_width = (window.outerWidth - window.innerWidth);
            b1browser.interface_height = (window.outerHeight - window.innerHeight);
            b1browser.cookies_enabled = navigator.cookieEnabled;
            b1browser.java_enabled = navigator.javaEnabled();
            document.cookie = \"browser_profile=\" + JSON.stringify(b1browser) + \"; \" + b1expires + \"; path=/; domain=".$this->d."\";
            if(navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var location = {};
                    location.latitude = position.coords.latitude;
                    location.longitude = position.coords.longitude;
                    location.altitude = position.coords.altitude;
                    document.cookie = \"geolocation=\" + JSON.stringify(location) + \"; \" + b1expires + \"; path=/; domain=".$this->d."\";
                });
            }
        </script>";
    }
}
?>
