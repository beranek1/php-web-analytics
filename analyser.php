<?php
/*
#-----------------------------------------
| b1 web analytics: analyser
| https://beranek1.github.io/webanalytics/
#-----------------------------------------
| made by beranek1
| https://github.com/beranek1
#-----------------------------------------
*/

class b1_analyser {
    public $total_requests = null;
    public $total_visitors = null;
    public $total_isps = null;
    public $total_networks = null;
    public $total_useragents = null;
    public $total_languages = null;
    public $total_countries = null;
    public $total_continents = null;
    public $top_requests = array();
    public $top_visitors = array();
    public $statistics = array();
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
    function country_to_continent($country) {
        $country_to_continent = array ("AD"=>"EU","AE"=>"AS","AF"=>"AS","AG"=>"NA","AI"=>"NA","AL"=>"EU","AM"=>"AS","AN"=>"NA","AO"=>"AF","AP"=>"AS","AR"=>"SA","AS"=>"OC","AT"=>"EU","AU"=>"OC","AW"=>"NA","AX"=>"EU","AZ"=>"AS","BA"=>"EU","BB"=>"NA","BD"=>"AS","BE"=>"EU","BF"=>"AF","BG"=>"EU","BH"=>"AS","BI"=>"AF","BJ"=>"AF","BL"=>"NA","BM"=>"NA","BN"=>"AS","BO"=>"SA","BR"=>"SA","BS"=>"NA","BT"=>"AS","BV"=>"AN","BW"=>"AF","BY"=>"EU","BZ"=>"NA","CA"=>"NA","CC"=>"AS","CD"=>"AF","CF"=>"AF","CG"=>"AF","CH"=>"EU","CI"=>"AF","CK"=>"OC","CL"=>"SA","CM"=>"AF","CN"=>"AS","CO"=>"SA","CR" => "NA","CU"=>"NA","CV"=>"AF","CX"=>"AS","CY"=>"AS","CZ"=>"EU","DE"=>"EU","DJ"=>"AF","DK"=>"EU","DM"=>"NA","DO"=>"NA","DZ"=>"AF","EC"=>"SA","EE"=>"EU","EG"=>"AF","EH"=>"AF","ER"=>"AF","ES"=>"EU","ET"=>"AF","EU"=>"EU","FI"=>"EU","FJ"=>"OC","FK"=>"SA","FM"=>"OC","FO"=>"EU","FR"=>"EU","FX"=>"EU","GA"=>"AF","GB"=>"EU","GD"=>"NA","GE"=>"AS","GF"=>"SA","GG"=>"EU","GH"=>"AF","GI"=>"EU","GL"=>"NA","GM"=>"AF","GN"=>"AF","GP"=>"NA","GQ"=>"AF","GR"=>"EU","GS"=>"AN","GT"=>"NA","GU"=>"OC","GW"=>"AF","GY"=>"SA","HK"=>"AS","HM"=>"AN","HN"=>"NA","HR"=>"EU","HT"=>"NA","HU"=>"EU","ID"=>"AS","IE"=>"EU","IL"=>"AS","IM"=>"EU","IN"=>"AS","IO"=>"AS","IQ"=>"AS","IR"=>"AS","IS"=>"EU","IT"=>"EU","JE"=>"EU","JM"=>"NA","JO"=>"AS","JP"=>"AS","KE"=>"AF","KG"=>"AS","KH"=>"AS","KI"=>"OC","KM"=>"AF","KN"=>"NA","KP"=>"AS","KR"=>"AS","KW"=>"AS","KY"=>"NA","KZ"=>"AS","LA"=>"AS","LB"=>"AS","LC"=>"NA","LI"=>"EU","LK"=>"AS","LR"=>"AF","LS"=>"AF","LT"=>"EU","LU"=>"EU","LV"=>"EU","LY"=>"AF","MA"=>"AF","MC"=>"EU","MD"=>"EU","ME"=>"EU","MF"=>"NA","MG"=>"AF","MH"=>"OC","MK"=>"EU","ML"=>"AF","MM"=>"AS","MN"=>"AS","MO"=>"AS","MP"=>"OC","MQ"=>"NA","MR"=>"AF","MS"=>"NA","MT"=>"EU","MU"=>"AF","MV"=>"AS","MW"=>"AF","MX"=>"NA","MY"=>"AS","MZ"=>"AF","NA"=>"AF","NC"=>"OC","NE"=>"AF","NF"=>"OC","NG"=>"AF","NI"=>"NA","NL"=>"EU","NO"=>"EU","NP"=>"AS","NR"=>"OC","NU"=>"OC","NZ"=>"OC","OM"=>"AS","PA"=>"NA","PE"=>"SA","PF"=>"OC","PG"=>"OC","PH"=>"AS","PK"=>"AS","PL"=>"EU","PM"=>"NA","PN"=>"OC","PR"=>"NA","PS"=>"AS","PT"=>"EU","PW"=>"OC","PY"=>"SA","QA"=>"AS","RE"=>"AF","RO"=>"EU","RS"=>"EU","RU"=>"EU","RW"=>"AF","SA"=>"AS","SB"=>"OC","SC"=>"AF","SD"=>"AF","SE"=>"EU","SG"=>"AS","SH"=>"AF","SI"=>"EU","SJ"=>"EU","SK"=>"EU","SL"=>"AF","SM"=>"EU","SN"=>"AF","SO"=>"AF","SR"=>"SA","ST"=>"AF","SV"=>"NA","SY"=>"AS","SZ"=>"AF","TC"=>"NA","TD"=>"AF","TF"=>"AN","TG"=>"AF","TH"=>"AS","TJ"=>"AS","TK"=>"OC","TL"=>"AS","TM"=>"AS","TN"=>"AF","TO"=>"OC","TR"=>"EU","TT"=>"NA","TV"=>"OC","TW"=>"AS","TZ"=>"AF","UA"=>"EU","UG"=>"AF","UM"=>"OC","US"=>"NA","UY"=>"SA","UZ"=>"AS","VA"=>"EU","VC"=>"NA","VE"=>"SA","VG"=>"NA","VI"=>"NA","VN"=>"AS","VU"=>"OC","WF"=>"OC","WS"=>"OC","YE"=>"AS","YT"=>"AF","ZA"=>"AF","ZM"=>"AF","ZW"=>"AF");
        return $country_to_continent[strtoupper($country)];
    }
    function get_tops($mysql, $table, $field, $start = null, $end = null, $timefield = "time") {
        $tops = array();
        $rows = null;
        if($start == null && $end == null) {
            $rows = $this->get_rows_array("SELECT `".$field."`, COUNT(*) FROM ".$table." GROUP BY `".$field."` ORDER BY COUNT(*) DESC;", $mysql);
        } else if($start != null && $end == null) {
            $rows = $this->get_rows_array("SELECT `".$field."`, COUNT(*) FROM ".$table." WHERE ".$timefield." BETWEEN DATE_ADD(NOW(),INTERVAL ".$start.") AND NOW() GROUP BY `".$field."` ORDER BY COUNT(*) DESC;", $mysql);
        } else {
            $rows = $this->get_rows_array("SELECT `".$field."`, COUNT(*) FROM ".$table." WHERE ".$timefield." BETWEEN DATE_ADD(NOW(),INTERVAL ".$start.") AND DATE_ADD(NOW(),INTERVAL ".$end.") GROUP BY `".$field."` ORDER BY COUNT(*) DESC;", $mysql);
        }
        foreach($rows as $row) {
            $tops[$row[0]] = $row[1];
        }
        return $tops;
    }
    function get_total_count($mysql, $table, $start = null, $end = null, $timefield = "time") {
        $row = null;
        if($start == null && $end == null) {
            $row = $this->get_one_row("SELECT COUNT(*) FROM ".$table.";", $mysql);
        } else if($start != null && $end == null) {
            $row = $this->get_one_row("SELECT COUNT(*) FROM ".$table." WHERE ".$timefield." BETWEEN DATE_ADD(NOW(),INTERVAL ".$start.") AND NOW();", $mysql);
        } else {
            $row = $this->get_one_row("SELECT COUNT(*) FROM ".$table." WHERE ".$timefield." BETWEEN DATE_ADD(NOW(),INTERVAL ".$start.") AND DATE_ADD(NOW(),INTERVAL ".$end.");", $mysql);
        }
        return $row[0];
    }
    function analyse_countries($mysql, $table, $field, $start = null, $end = null, $timefield = "time") {
        $total_countries = 0;
        $rows = null;
        if($start == null && $end == null) {
            $rows = $this->get_rows_array("SELECT `".$field."`, COUNT(*) FROM ".$table." GROUP BY `".$field."` ORDER BY COUNT(*) DESC;", $mysql);
        } else if($start != null && $end == null) {
            $rows = $this->get_rows_array("SELECT `".$field."`, COUNT(*) FROM ".$table." WHERE ".$timefield." BETWEEN DATE_ADD(NOW(),INTERVAL ".$start.") AND NOW() GROUP BY `".$field."` ORDER BY COUNT(*) DESC;", $mysql);
        } else {
            $rows = $this->get_rows_array("SELECT `".$field."`, COUNT(*) FROM ".$table." WHERE ".$timefield." BETWEEN DATE_ADD(NOW(),INTERVAL ".$start.") AND DATE_ADD(NOW(),INTERVAL ".$end.") GROUP BY `".$field."` ORDER BY COUNT(*) DESC;", $mysql);
        }
        $top_countries = array();
        $top_continents = array();
        $total_continents = 0;
        foreach($rows as $row) {
            $country = $row[0];
            if($country != "" && $country != null) {
                $top_countries[$country] = $row[1];
                if(!array_key_exists($country, $top_countries)) {
                    $total_countries = $total_countries + 1;
                }
                $continent = $this->country_to_continent($country);
                if(!array_key_exists($continent, $top_continents)) {
                    $total_continents = $total_continents + 1;
                }
                $top_continents[$continent] = $top_continents[$continent] + $row[1];
            } else {
                $top_countries["?"] = $row[1];
                $top_continents["?"] = $row[1];
            }
        }
        arsort($top_continents);
        return array("top_countries" => $top_countries, "top_continents" => $top_continents, "total_countries" => $total_countries, "total_continents" => $total_continents);
    }
    function analyse_time($mysql, $time = null) {
        $timespan = null;
        $times = array("24h" => array("start" => "-24 HOUR", "end" => null), "7days" => array("start" => "-7 DAY", "end" => null), "30days" => array("start" => "-30 DAY", "end" => null), "365days" => array("start" => "365 DAY", "end" => null));
        $start = null;
        $end = null;
        if($time != null) {
            if(array_key_exists($time, $times)) {
                $start = $times[$time]["start"];
                $end = $times[$time]["end"];
            }
        }
        $total_requests = $this->get_total_count($mysql, "requests", $start, $end);
        $total_visitors = $this->get_total_count($mysql, "browsers", $start, $end);
        $total_isps = $this->get_total_count($mysql, "isps", $start, $end);
        $total_networks = $this->get_total_count($mysql, "networks", $start, $end);
        $total_useragents = $this->get_total_count($mysql, "agents", $start, $end);
        $top_requests = $this->analyse_countries($mysql, "requests", "visitor_country", $start, $end);
        $top_visitors = $this->analyse_countries($mysql, "browsers", "country", $start, $end);
        $top_requests["uris"] = $this->get_tops($mysql, "requests", "uri", $start, $end);
        $timespan = array("total_requests" => $total_requests, "total_visitors" => $total_visitors, "total_isps" => $total_isps, "total_networks" => $total_networks, "total_useragents" => $total_useragents, "top_requests" => $top_requests, "top_visitors" => $top_visitors);
        return $timespan;
    }
    function __construct($mysql) {
        $this->statistics["alltime"] = analyse_time($mysql);
    }
}
?>
