<?php
/*
#-----------------------------------------
| WebAnalytics: Statistics
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

$country_to_continent = array ("AD"=>"EU","AE"=>"AS","AF"=>"AS","AG"=>"NA","AI"=>"NA","AL"=>"EU","AM"=>"AS","AN"=>"NA","AO"=>"AF","AP"=>"AS","AR"=>"SA","AS"=>"OC","AT"=>"EU","AU"=>"OC","AW"=>"NA","AX"=>"EU","AZ"=>"AS","BA"=>"EU","BB"=>"NA","BD"=>"AS","BE"=>"EU","BF"=>"AF","BG"=>"EU","BH"=>"AS","BI"=>"AF","BJ"=>"AF","BL"=>"NA","BM"=>"NA","BN"=>"AS","BO"=>"SA","BR"=>"SA","BS"=>"NA","BT"=>"AS","BV"=>"AN","BW"=>"AF","BY"=>"EU","BZ"=>"NA","CA"=>"NA","CC"=>"AS","CD"=>"AF","CF"=>"AF","CG"=>"AF","CH"=>"EU","CI"=>"AF","CK"=>"OC","CL"=>"SA","CM"=>"AF","CN"=>"AS","CO"=>"SA","CR,NA","CU"=>"NA","CV"=>"AF","CX"=>"AS","CY"=>"AS","CZ"=>"EU","DE"=>"EU","DJ"=>"AF","DK"=>"EU","DM"=>"NA","DO"=>"NA","DZ"=>"AF","EC"=>"SA","EE"=>"EU","EG"=>"AF","EH"=>"AF","ER"=>"AF","ES"=>"EU","ET"=>"AF","EU"=>"EU","FI"=>"EU","FJ"=>"OC","FK"=>"SA","FM"=>"OC","FO"=>"EU","FR"=>"EU","FX"=>"EU","GA"=>"AF","GB"=>"EU","GD"=>"NA","GE"=>"AS","GF"=>"SA","GG"=>"EU","GH"=>"AF","GI"=>"EU","GL"=>"NA","GM"=>"AF","GN"=>"AF","GP"=>"NA","GQ"=>"AF","GR"=>"EU","GS"=>"AN","GT"=>"NA","GU"=>"OC","GW"=>"AF","GY"=>"SA","HK"=>"AS","HM"=>"AN","HN"=>"NA","HR"=>"EU","HT"=>"NA","HU"=>"EU","ID"=>"AS","IE"=>"EU","IL"=>"AS","IM"=>"EU","IN"=>"AS","IO"=>"AS","IQ"=>"AS","IR"=>"AS","IS"=>"EU","IT"=>"EU","JE"=>"EU","JM"=>"NA","JO"=>"AS","JP"=>"AS","KE"=>"AF","KG"=>"AS","KH"=>"AS","KI"=>"OC","KM"=>"AF","KN"=>"NA","KP"=>"AS","KR"=>"AS","KW"=>"AS","KY"=>"NA","KZ"=>"AS","LA"=>"AS","LB"=>"AS","LC"=>"NA","LI"=>"EU","LK"=>"AS","LR"=>"AF","LS"=>"AF","LT"=>"EU","LU"=>"EU","LV"=>"EU","LY"=>"AF","MA"=>"AF","MC"=>"EU","MD"=>"EU","ME"=>"EU","MF"=>"NA","MG"=>"AF","MH"=>"OC","MK"=>"EU","ML"=>"AF","MM"=>"AS","MN"=>"AS","MO"=>"AS","MP"=>"OC","MQ"=>"NA","MR"=>"AF","MS"=>"NA","MT"=>"EU","MU"=>"AF","MV"=>"AS","MW"=>"AF","MX"=>"NA","MY"=>"AS","MZ"=>"AF","NA"=>"AF","NC"=>"OC","NE"=>"AF","NF"=>"OC","NG"=>"AF","NI"=>"NA","NL"=>"EU","NO"=>"EU","NP"=>"AS","NR"=>"OC","NU"=>"OC","NZ"=>"OC","OM"=>"AS","PA"=>"NA","PE"=>"SA","PF"=>"OC","PG"=>"OC","PH"=>"AS","PK"=>"AS","PL"=>"EU","PM"=>"NA","PN"=>"OC","PR"=>"NA","PS"=>"AS","PT"=>"EU","PW"=>"OC","PY"=>"SA","QA"=>"AS","RE"=>"AF","RO"=>"EU","RS"=>"EU","RU"=>"EU","RW"=>"AF","SA"=>"AS","SB"=>"OC","SC"=>"AF","SD"=>"AF","SE"=>"EU","SG"=>"AS","SH"=>"AF","SI"=>"EU","SJ"=>"EU","SK"=>"EU","SL"=>"AF","SM"=>"EU","SN"=>"AF","SO"=>"AF","SR"=>"SA","ST"=>"AF","SV"=>"NA","SY"=>"AS","SZ"=>"AF","TC"=>"NA","TD"=>"AF","TF"=>"AN","TG"=>"AF","TH"=>"AS","TJ"=>"AS","TK"=>"OC","TL"=>"AS","TM"=>"AS","TN"=>"AF","TO"=>"OC","TR"=>"EU","TT"=>"NA","TV"=>"OC","TW"=>"AS","TZ"=>"AF","UA"=>"EU","UG"=>"AF","UM"=>"OC","US"=>"NA","UY"=>"SA","UZ"=>"AS","VA"=>"EU","VC"=>"NA","VE"=>"SA","VG"=>"NA","VI"=>"NA","VN"=>"AS","VU"=>"OC","WF"=>"OC","WS"=>"OC","YE"=>"AS","YT"=>"AF","ZA"=>"AF","ZM"=>"AF","ZW"=>"AF");

$web_analytics_db->connect();
$total_requests = $web_analytics_db->count("wa_requests");
if($total_requests == 0) {
    echo "Not enough data collected yet.<br>";
    echo "<a href=\"https://webanalytics.one\">WebAnalytics</a>";
    return;
}
$total_visitors = $web_analytics_db->count("wa_browsers");
$total_networks = $web_analytics_db->count("wa_ips");
$total_isps = $web_analytics_db->count("wa_isps");
$top_countries = array();
$top_continents = array();
$total_continents = 0;
foreach($web_analytics_db->query("SELECT `visitor_country`, COUNT(*) FROM wa_requests GROUP BY `visitor_country` ORDER BY COUNT(*) DESC;") as $country) {
    if($country[0] != "" && $country[0] != null) {
        $top_countries[$country[0]] = $country[1];
        $continent = $country_to_continent[strtoupper($country[0])];
        if(!array_key_exists($continent, $top_continents)) {
            $total_continents = $total_continents + 1;
        }
        $top_continents[$continent] = $top_continents[$continent] + $country[1];
    } else {
        $top_countries["?"] = $country[1];
        $top_continents["?"] = $country[1];
    }
}
arsort($top_continents);
$total_countries = 0;
$top_countriesvo = array();
foreach($web_analytics_db->query("SELECT `country`, COUNT(*) FROM wa_browsers GROUP BY `country` ORDER BY COUNT(*) DESC;") as $country) {
    if($country[0] != "" && $country[0] != null) {
        $top_countriesvo[$country[0]] = $country[1];
        $total_countries = $total_countries + 1;
    } else {
        $top_countriesvo["?"] = $country[1];
    }
}
$top_languages = array();
$total_languages = 0;
foreach($tplngsr = $web_analytics_db->query("SELECT `language`, COUNT(*) FROM wa_browsers GROUP BY `language` ORDER BY COUNT(*) DESC;") as $language) {
    if($language != "" && $language != null) {
        $top_languages[$language[0]] = $language[1];
        $total_languages = $total_languages + 1;
    } else {
        $top_languages["?"] = $language[1];
    }
}
$top_useragents = array();
foreach($web_analytics_db->query("SELECT `user_agent`, COUNT(*) FROM wa_browsers GROUP BY `user_agent` ORDER BY COUNT(*) DESC;") as $useragent) {
    $top_useragents[$useragent[0]] = $useragent[1];
}
$top_isps = array();
foreach($web_analytics_db->query("SELECT `isp_id`, COUNT(*) FROM wa_ips GROUP BY `isp_id` ORDER BY COUNT(*) DESC;") as $isp) {
    $top_isps[$isp[0]] = $isp[1];
}
$top_uris = array();
foreach($web_analytics_db->query("SELECT `uri`, COUNT(*) FROM wa_requests GROUP BY `uri` ORDER BY COUNT(*) DESC;") as $uri) {
    $top_uris[$uri[0]] = $uri[1];
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="robots" content="noindex,nofollow">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <title>WebAnalytics: Statistics</title>
        <script src="wa.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark">
            <span class="navbar-brand mb-0 h1">Website statistics</span>
        </nav>
        <div class="container-fluid">
            <div class="jumbotron row">
                <div class="col">
                    <h2>All time statistics</h2>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Requests
                            <span class="badge badge-primary badge-pill"><?php echo $total_requests; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Unique visitors
                            <span class="badge badge-primary badge-pill"><?php echo $total_visitors; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Networks
                            <span class="badge badge-primary badge-pill"><?php echo $total_networks; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ISPs
                            <span class="badge badge-primary badge-pill"><?php echo $total_isps; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Countries
                            <span class="badge badge-primary badge-pill"><?php echo $total_countries; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Continents
                            <span class="badge badge-primary badge-pill"><?php echo $total_continents; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Languages
                            <span class="badge badge-primary badge-pill"><?php echo $total_languages; ?></span>
                        </li>
                    </ul>
                </div>
                <div class="col">
                    <h2>Average visitor</h2>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Requests
                            <span class="badge badge-primary badge-pill"><?php echo "".round(($total_requests/$total_visitors), 2).""; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Country
                            <span class="badge badge-primary badge-pill"><?php echo "".array_keys($top_countriesvo)[0].""; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Language
                            <span class="badge badge-primary badge-pill"><?php echo "".array_keys($top_languages)[0].""; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            User agent
                            <span class="badge badge-primary badge-pill"><?php echo "".array_keys($top_useragents)[0].""; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ISP
                            <span class="badge badge-primary badge-pill"><?php echo "".array_keys($top_isps)[0].""; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        <div>
            <h2>Countries ordered by requests</h2>
            <table class="table">
                <thead class="thead-dark">
                    <tr><th scope="col">Country code</th><th scope="col">requests</th><th scope="col">proportion</th></tr>
                </thead>
                <?php foreach ($top_countries as $key => $value) { echo "<tr><td scope='row'>".$key."</td><td>".$value."</td><td><div class='progress'><div class='progress-bar' role='progressbar' style='width: ".(($value/$total_requests)*100)."%' aria-valuenow='".(($value/$total_requests)*100)."' aria-valuemin='0' aria-valuemax='100'>".round(($value/$total_requests)*100, 2)."%</div></div></td></tr>"; } ?>
                <tr><th>Total</th><th><?php echo $total_requests; ?></th></tr>
            </table>
        </div>
        <div>
            <h2>Countries ordered by visitors</h2>
            <table class="table">
                <thead class="thead-dark">
                    <tr><th scope="col">Country code</th><th scope="col">visitors</th><th scope="col">proportion</th></tr>
                </thead>
                <?php foreach ($top_countriesvo as $key => $value) { echo "<tr><td scope='row'>".$key."</td><td>".$value."</td><td><div class='progress'><div class='progress-bar' role='progressbar' style='width: ".(($value/$total_visitors)*100)."%' aria-valuenow='".(($value/$total_visitors)*100)."' aria-valuemin='0' aria-valuemax='100'>".round(($value/$total_visitors)*100, 2)."%</div></div></td></tr>"; } ?>
                <tr><th>Total</th><th><?php echo $total_visitors; ?></th></tr>
            </table>
        </div>
        <div>
            <h2>Continents ordered by requests</h2>
            <table class="table">
                <thead class="thead-dark">
                    <tr><th scope="col">Continent code</th><th scope="col">requests</th><th scope="col">proportion</th></tr>
                </thead>
                <?php foreach ($top_continents as $key => $value) { echo "<tr><td scope='row'>".$key."</td><td>".$value."</td><td><div class='progress'><div class='progress-bar' role='progressbar' style='width: ".(($value/$total_requests)*100)."%' aria-valuenow='".(($value/$total_requests)*100)."' aria-valuemin='0' aria-valuemax='100'>".round(($value/$total_requests)*100, 2)."%</div></div></td></tr>"; } ?>
                <tr><th>Total</th><th><?php echo $total_requests; ?></th></tr>
            </table>
        </div>
        <div>
            <h2>Languages ordered by visitors</h2>
            <table class="table">
                <thead class="thead-dark">
                    <tr><th scope="col">Language</th><th scope="col">visitors</th><th scope="col">proportion</th></tr>
                </thead>
                <?php foreach ($top_languages as $key => $value) { echo "<tr><td scope='row'>".$key."</td><td>".$value."</td><td><div class='progress'><div class='progress-bar' role='progressbar' style='width: ".(($value/$total_visitors)*100)."%' aria-valuenow='".(($value/$total_visitors)*100)."' aria-valuemin='0' aria-valuemax='100'>".round(($value/$total_visitors)*100, 2)."%</div></div></td></tr>"; } ?>
                <tr><th>Total</th><th><?php echo $total_visitors; ?></th></tr>
            </table>
        </div>
        <div>
            <h2>Top user agents ordered by users</h2>
            <table class="table">
                <thead class="thead-dark">
                    <tr><th scope="col">Agent</th><th scope="col">users</th><th scope="col">proportion</th></tr>
                </thead>
                <?php foreach ($top_useragents as $key => $value) { echo "<tr><td scope='row'>".$key."</td><td>".$value."</td><td><div class='progress'><div class='progress-bar' role='progressbar' style='width: ".(($value/$total_visitors)*100)."%' aria-valuenow='".(($value/$total_visitors)*100)."' aria-valuemin='0' aria-valuemax='100'>".round(($value/$total_visitors)*100, 2)."%</div></div></td></tr>"; } ?>
                <tr><th>Total</th><th><?php echo $total_visitors; ?></th></tr>
            </table>
        </div>
        <div>
            <h2>Top isps ordered by networks</h2>
            <table class="table">
                <thead class="thead-dark">
                    <tr><th scope="col">ISP</th><th scope="col">networks</th><th scope="col">proportion</th></tr>
                </thead>
                <?php foreach ($top_isps as $key => $value) { echo "<tr><td scope='row'>".$key."</td><td>".$value."</td><td><div class='progress'><div class='progress-bar' role='progressbar' style='width: ".(($value/$total_isps)*100)."%' aria-valuenow='".(($value/$total_isps)*100)."' aria-valuemin='0' aria-valuemax='100'>".round(($value/$total_isps)*100, 2)."%</div></div></td></tr>"; } ?>
                <tr><th>Total</th><th><?php echo $total_isps; ?></th></tr>
            </table>
        </div>
        <div>
            <h2>URIs/Pages ordered by requests</h2>
            <table class="table">
                <thead class="thead-dark">
                    <tr><th scope="col">URI</th><th scope="col">requests</th><th scope="col">proportion</th></tr>
                </thead>
                <?php foreach ($top_uris as $key => $value) { echo "<tr><td scope='row'>".$key."</td><td>".$value."</td><td><div class='progress'><div class='progress-bar' role='progressbar' style='width: ".(($value/$total_requests)*100)."%' aria-valuenow='".(($value/$total_requests)*100)."' aria-valuemin='0' aria-valuemax='100'>".round(($value/$total_requests)*100, 2)."%</div></div></td></tr>"; } ?>
                <tr><th>Total</th><th><?php echo $total_requests; ?></th></tr>
            </table>
        </div>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    </body>
    <nav class="navbar navbar-light bg-light">
        <span class="navbar-text">
                <a href="https://webanalytics.one">Powered by WebAnalytics</a>
        </span>
    </nav>
</html>
<?php
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
    
    function get_rows_array($query) {
        return $this->query($query);
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
?>