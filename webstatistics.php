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

if (!function_exists("array_key_last")) {
    function array_key_last($array) {
        if (!is_array($array) || empty($array)) {
            return NULL;
        }
        return array_keys($array)[count($array)-1];
    }
}

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
$top_countries = array();
$top_continents = array();
$total_continents = 0;
foreach($web_analytics_db->query("SELECT `visitor_country`, COUNT(*) FROM wa_requests GROUP BY `visitor_country` ORDER BY COUNT(*) DESC;") as $country) {
    if($country[0] != "" && $country[0] != null) {
        $top_countries[$country[0]] = $country[1];
        $continent = $country_to_continent[strtoupper($country[0])];
        if(!array_key_exists($continent, $top_continents)) {
            $top_continents[$continent] = $country[1];
            $total_continents = $total_continents + 1;
        } else {
            $top_continents[$continent] = $top_continents[$continent] + $country[1];
        }
    } else {
        $top_countries["?"] = $country[1];
        $top_continents["?"] = $country[1];
    }
}
$top_origins = array_merge($top_countries, $top_continents);
asort($top_origins);
arsort($top_continents);
$total_countries = 0;
$top_countriesvo = array();
$top_continentsvo = array();
foreach($web_analytics_db->query("SELECT `country`, COUNT(*) FROM wa_browsers GROUP BY `country` ORDER BY COUNT(*) DESC;") as $country) {
    if($country[0] != "" && $country[0] != null) {
        $top_countriesvo[$country[0]] = $country[1];
        $continent = $country_to_continent[strtoupper($country[0])];
        if(!array_key_exists($continent, $top_continentsvo)) {
            $top_continentsvo[$continent] = $country[1];
        } else {
            $top_continentsvo[$continent] = $top_continentsvo[$continent] + $country[1];
        }
        $total_countries = $total_countries + 1;
    } else {
        $top_countriesvo["?"] = $country[1];
    }
}
$top_originsvo = array_merge($top_countriesvo, $top_continentsvo);
$top_languages = array();
$total_languages = 0;
foreach($web_analytics_db->query("SELECT `language`, COUNT(*) FROM wa_browsers GROUP BY `language` ORDER BY COUNT(*) DESC;") as $language) {
    if($language[0] != "" && $language[0] != null) {
        $top_languages[$language[0]] = $language[1];
        $total_languages = $total_languages + 1;
    } else {
        $top_languages["?"] = $language[1];
    }
}
$top_useragents = array();
$top_browsers = array();
$top_oss = array();
foreach($web_analytics_db->query("SELECT `user_agent`, COUNT(*) FROM wa_browsers GROUP BY `user_agent` ORDER BY COUNT(*) DESC LIMIT 10;") as $useragent) {
    $top_useragents[$useragent[0]] = $useragent[1];
    $uaa = analyse_user_agent($useragent[0]);
    if(isset($top_browsers[$uaa["browser"]["name"]])) {
        $top_browsers[$uaa["browser"]["name"]] += $useragent[1];
    } else {
        $top_browsers[$uaa["browser"]["name"]] = $useragent[1];
    }
    if(isset($top_oss[$uaa["os"]["name"]])) {
        $top_oss[$uaa["os"]["name"]] += $useragent[1];
    } else {
        $top_oss[$uaa["os"]["name"]] = $useragent[1];
    }
}
$total_isps = 0;
$top_isps = array();
foreach($web_analytics_db->query("SELECT `isp`, COUNT(*) FROM wa_ips GROUP BY `isp` ORDER BY COUNT(*) DESC LIMIT 10;") as $isp) {
    if($isp[0] != "" && $isp[0] != null) {
        $top_isps[$isp[0]] = $isp[1];
        $total_isps++;
    } else {
        $top_isps["?"] = $isp[1];
    }
}
$top_uris = array();
foreach($web_analytics_db->query("SELECT `uri`, COUNT(*) FROM wa_requests GROUP BY `uri` ORDER BY COUNT(*) DESC LIMIT 10;") as $uri) {
    $top_uris[$uri[0]] = $uri[1];
}
$last_requests = array();
$last_requests_by_daytime = array();
$last_requests_by_day = array();
$last_requests_by_weekday = array();
$last_visitors = array();
$last_visitors_by_daytime = array();
$last_visitors_by_day = array();
$last_visitors_by_weekday = array();
foreach($web_analytics_db->query("SELECT `time`, `browser_id` FROM wa_requests ORDER BY `time` LIMIT 1000;") as $request) {
    $time = $request[0];
    $daytime = date("[H, 0, 0]", strtotime($time));
    $day = date("Y, m, d", strtotime($time));
    $weekday = date("l", strtotime($time));
    if(isset($last_requests[$time])) {
        $last_requests[$time] += 1;
    } else {
        $last_requests[$time] = 1;
    }
    if(isset($last_requests_by_day[$day])) {
        $last_requests_by_day[$day] += 1;
    } else {
        $last_requests_by_day[$day] = 1;
    }
    if(isset($last_requests_by_weekday[$weekday])) {
        $last_requests_by_weekday[$weekday] += 1;
    } else {
        $last_requests_by_weekday[$weekday] = 1;
    }
    if(isset($last_requests_by_daytime[$daytime])) {
        $last_requests_by_daytime[$daytime] += 1;
    } else {
        $last_requests_by_daytime[$daytime] = 1;
    }
    if(isset($last_visitors[$time])) {
        if(!isset($last_visitors[$time][$request[1]])) {
            $last_visitors[$time][$request[1]] = 1;
        }
    } else {
        $last_visitors[$time] = array($request[1] => 1);
    }
    if(isset($last_visitors_by_day[$day])) {
        if(!isset($last_visitors_by_day[$day][$request[1]])) {
            $last_visitors_by_day[$day][$request[1]] = 1;
        }
    } else {
        $last_visitors_by_day[$day] = array($request[1] => 1);
    }
    if(isset($last_visitors_by_weekday[$weekday])) {
        if(!isset($last_visitors_by_weekday[$weekday][$request[1]])) {
            $last_visitors_by_weekday[$weekday][$request[1]] = 1;
        }
    } else {
            $last_visitors_by_weekday[$weekday] = array($request[1] => 1);
    }
    if(isset($last_visitors_by_daytime[$daytime])) {
        if(!isset($last_visitors_by_daytime[$daytime][$request[1]])) {
            $last_visitors_by_daytime[$daytime][$request[1]] = 1;
        }
    } else {
        $last_visitors_by_daytime[$daytime] = array($request[1] => 1);
    }
}
ksort($last_requests_by_daytime);
ksort($last_visitors_by_daytime);
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
        <nav class="navbar navbar-expand-md navbar-dark bg-dark">
            <a class="navbar-brand" href="#">WebAnalytics</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarToggler">
                <ul class="nav navbar-nav mr-auto mt-2 mt-lg-0" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Home <span class="sr-only">(current)</span></a>
                    </li>
                </ul>
            </div>
            <button class="btn btn-outline-success my-2 my-sm-0" onclick="window.location.reload();">Refresh</button>
        </nav>
        <div class="container-fluid tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
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
                                Browser
                                <span class="badge badge-primary badge-pill"><?php echo "".array_keys($top_browsers)[0].""; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                OS
                                <span class="badge badge-primary badge-pill"><?php echo "".array_keys($top_oss)[0].""; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                ISP
                                <span class="badge badge-primary badge-pill"><?php echo "".array_keys($top_isps)[0].""; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                <h1>Traffic</h1>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                Traffic
                            </div>
                            <div class="card-body">
                                <div id="rbyd" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                Week day
                            </div>
                            <div class="card-body">
                                <div id="rbywd" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                Day time
                            </div>
                            <div class="card-body">
                                <div id="rbydt" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                Origin
                            </div>
                            <div class="card-body">
                                <div id="obyr" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                Language
                            </div>
                            <div class="card-body">
                                <div id="lbyv" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                ISP
                            </div>
                            <div class="card-body">
                                <div id="ispbyn" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                Browser
                            </div>
                            <div class="card-body">
                                <div id="bbyv" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                Operating system
                            </div>
                            <div class="card-body">
                                <div id="obyv" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                Pages
                            </div>
                            <div class="card-body">
                                <div id="pbyr" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
        google.charts.load('current', {'packages':['bar', 'line']});
        google.charts.setOnLoadCallback(drawCharts);
        var reqvisOptions = {
                series: {
                    0: { axis: 'requests' },
                    1: { axis: 'visitors' }
                },
                axes: {
                    y: {
                        distance: {label: 'requests'},
                        brightness: {side: 'right', label: 'visitors'}
                    }
                }
            };
        function drawobyrChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Origin');
            data.addColumn('number', 'Requests');
            data.addColumn('number', 'Visitors');
            data.addRows([
                <?php
                $i = 0;
                foreach ($top_origins as $key => $value) {
                    if($i == 0) {
                        echo "['".$key."', ".$value.", ".$top_originsvo[$key]."]";
                        $i++;
                    } else {
                        echo ",['".$key."', ".$value.", ".$top_originsvo[$key]."]";
                    }
                }
                ?>
            ]);
            var chart = new google.charts.Bar(document.getElementById('obyr'));
            chart.draw(data, {});
        }
        function drawlbyvChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Language');
            data.addColumn('number', 'Visitors');
            data.addRows([
                <?php
                $i = 0;
                foreach ($top_languages as $key => $value) {
                    if($i == 0) {
                        echo "['".$key."', ".$value."]";
                        $i++;
                    } else {
                        echo ",['".$key."', ".$value."]";
                    }
                }
                ?>
            ]);
            var chart = new google.charts.Bar(document.getElementById('lbyv'));
            chart.draw(data, {});
        }
        function drawbbyvChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Browser');
            data.addColumn('number', 'Visitors');
            data.addRows([
                <?php
                $i = 0;
                foreach ($top_browsers as $key => $value) {
                    if($i == 0) {
                        echo "['".$key."', ".$value."]";
                        $i++;
                    } else {
                        echo ",['".$key."', ".$value."]";
                    }
                }
                ?>
            ]);
            var chart = new google.charts.Bar(document.getElementById('bbyv'));
            chart.draw(data, {});
        }
        function drawobyvChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'OS');
            data.addColumn('number', 'Visitors');
            data.addRows([
                <?php
                $i = 0;
                foreach ($top_oss as $key => $value) {
                    if($i == 0) {
                        echo "['".$key."', ".$value."]";
                        $i++;
                    } else {
                        echo ",['".$key."', ".$value."]";
                    }
                }
                ?>
            ]);
            var chart = new google.charts.Bar(document.getElementById('obyv'));
            chart.draw(data, {});
        }
        function drawispbynChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'ISP');
            data.addColumn('number', 'networks');
            data.addRows([
                <?php
                $i = 0;
                foreach ($top_isps as $key => $value) {
                    if($i == 0) {
                        echo "['".$key."', ".$value."]";
                        $i++;
                    } else {
                        echo ",['".$key."', ".$value."]";
                    }
                }
                ?>
            ]);
            var chart = new google.charts.Bar(document.getElementById('ispbyn'));
            chart.draw(data, {});
        }
        function drawrbydChart() {
            var data = google.visualization.arrayToDataTable([
            ['Day', 'Requests', 'Visitors']
            <?php
                foreach ($last_requests_by_day as $key => $value) {
                    echo ",[new Date(".$key."), ".$value.", ".count($last_visitors_by_day[$key])."]";
                }
                ?>
            ]);

            var chart = new google.charts.Line(document.getElementById('rbyd'));
            chart.draw(data, reqvisOptions);
        }
        function drawrbywdChart() {
            var data = google.visualization.arrayToDataTable([
            ['Day', 'Requests', 'Visitors']
            <?php
                foreach ($last_requests_by_weekday as $key => $value) {
                    echo ",['".$key."', ".$value.", ".count($last_visitors_by_weekday[$key])."]";
                }
                ?>
            ]);
            var chart = new google.charts.Bar(document.getElementById('rbywd'));
            chart.draw(data, {});
        }
        function drawrbydtChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('timeofday', 'Time of Day');
            data.addColumn('number', 'Requests');
            data.addColumn('number', 'Visitors');
            data.addRows([
            <?php
                $i = 0;
                foreach ($last_requests_by_daytime as $key => $value) {
                    if($i == 0) {
                        echo "[".$key.", ".$value.", ".count($last_visitors_by_daytime[$key])."]";
                        $i++;
                    } else {
                        echo ",[".$key.", ".$value.", ".count($last_visitors_by_daytime[$key])."]";
                    }
                }
                ?>
            ]);

            var chart = new google.charts.Bar(document.getElementById('rbydt'));
            chart.draw(data, {});
        }
        function drawpbyrChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Page');
            data.addColumn('number', 'Views');
            data.addRows([
                <?php
                $i = 0;
                foreach ($top_uris as $key => $value) {
                    if($i == 0) {
                        echo "['".$key."', ".$value."]";
                        $i++;
                    } else {
                        echo ",['".$key."', ".$value."]";
                    }
                }
                ?>
            ]);
            var chart = new google.charts.Bar(document.getElementById('pbyr'));
            chart.draw(data, {});
        }
        function drawCharts() {
            drawobyrChart();
            drawlbyvChart();
            drawbbyvChart();
            drawobyvChart();
            drawispbynChart();
            drawrbydChart();
            drawrbywdChart();
            drawrbydtChart();
            drawpbyrChart();
        }
        $(window).resize(function(){
            drawCharts();
        });
        $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
            drawCharts();
        });
    </script>
    </body>
    <nav class="navbar navbar-light bg-light">
        <span class="navbar-text">
                <a href="https://webanalytics.one">Powered by WebAnalytics</a> / <a href="https://getbootstrap.com">Bootstrap</a> / <a href="https://developers.google.com/chart/">Google Charts</a>
        </span>
    </nav>
</html>
<?php
/* UAA */

function analyse_user_agent($user_agent) {
    $result = array();
    $gecko = preg_match("/Mozilla\/\d[\d.]* \([A-Za-z0-9_.\- ;:\/]*\) Gecko\/\d+/i", $user_agent);
    $webkit = preg_match("/Mozilla\/\d[\d.]* \([A-Za-z0-9_.\- ;:\/]*\) AppleWebKit\/\d[\d.]* \(KHTML, like Gecko\)/i", $user_agent);
    if(preg_match_all("/\w+\/\d[\d.]*/", $user_agent, $matches)) {
        $browser = preg_split("/\//",$matches[0][array_key_last($matches[0])]);
        $trident = (preg_match("/trident/i", $browser[0]) && !$gecko && !$webkit);
        if($webkit) {
            if(preg_match("/safari/i", $browser[0])) {
                $browser = preg_split("/\//",$matches[0][2]);
                $i = 3;
                while((preg_match("/version/i", $browser[0]) || preg_match("/mobile/i", $browser[0])) && isset($matches[0][$i])) {
                    $browser = preg_split("/\//",$matches[0][$i]);
                    $i++;
                }
            }
        }
    }
    if(preg_match("/\([A-Za-z0-9_.\- ;:\/]*\)/", $user_agent, $match)) {
        $platforms = preg_split("/; /", preg_replace("/\)/", "", preg_replace("/\(/", "", $match[0])));
        if($trident) {
            $browser = preg_split("/ /",$platforms[1]);
            if(preg_match("/msie/i", $browser[0])) {
                $os = preg_split("/ \d/", preg_replace("/ nt/i", "",$platforms[2]));
                $osv = preg_split("/ /",$platforms[2]);
                if(preg_match("/xbox/i", $platforms[array_key_last($platforms)])) {
                    $result["device"]["name"] = $platforms[array_key_last($platforms)];
                }
            } else {
                $browser[0] = "msie";
                $version = preg_split("/:/", $platforms[array_key_last($platforms)]);
                $browser[1] = $version[1];
            }
        }
        if(preg_match("/windows/i", $platforms[0])) {
            $os = preg_split("/ \d/", preg_replace("/ nt/i", "",$platforms[0]));
            $osv = preg_split("/ /",$platforms[0]);
            if(preg_match("/phone/i", $os[0])) {
                $result["device"]["name"] = $platforms[array_key_last($platforms)-1]." ".$platforms[array_key_last($platforms)];
            }
            if(preg_match("/xbox/i", $platforms[array_key_last($platforms)])) {
                $result["device"]["name"] = $platforms[array_key_last($platforms)];
            }
            if(isset($platforms[2]) && preg_match("/x\d[\d]*/", $platforms[2])) {
                $result["device"]["cpu"] = $platforms[2];
            }
        } else if(preg_match("/linux/i", $platforms[0])) {
            $i = preg_match("/u/i", $platforms[1]) ? 2 : 1;
            $os = preg_split("/ \d/",$platforms[$i]);
            if(preg_match("/android/i", $os[0])) {
                $osv = preg_split("/ /",$platforms[$i]);
            } else {
                $os = preg_split("/ /",$platforms[0]);
                if(isset($os[1])) {
                    $result["device"]["cpu"] = $os[1];
                }
            }
            foreach ($platforms as $property) {
                if(preg_match("/build/i", $property)) {
                    $device = preg_split("/ build/i", $property);
                    $result["device"]["name"] = $device[0];
                }
            }
        } else if(preg_match("/linux/i", $platforms[1]) || preg_match("/cros/i", $platforms[1]) || preg_match("/ubuntu/i", $platforms[1])) {
            $os = preg_split("/ /",$platforms[1]);
            if(isset($os[1])) {
                $result["device"]["cpu"] = $os[1];
            }
        } else if(preg_match("/macintosh/i", $platforms[0])) {
            $os = preg_split("/ \d/",preg_replace("/intel /i", "", $platforms[1]));
            $osv = preg_split("/ /",$platforms[1]);
            $result["device"]["name"] = $platforms[0];
        } else if(preg_match("/iphone/i", $platforms[0]) || preg_match("/ipad/i", $platforms[0]) || preg_match("/ipod/i", $platforms[0])) {
            $os = preg_split("/ \d/",preg_replace("/cpu /i", "", $platforms[1]));
            $osv = preg_split("/ /", preg_replace("/ like mac os x/i", "", $platforms[1]));
            $result["device"]["name"] = $platforms[0];
        } else if(preg_match("/android/i", $platforms[0])) {
            $os = preg_split("/ \d/",$platforms[0]);
            $osv = preg_split("/ /",$platforms[0]);
            $result["device"]["name"] = $platforms[1];
        }
        if(isset($os)) {
            $result["os"]["name"] = $os[0];
        }
        if(isset($osv)) {
            $result["os"]["version"] = $osv[array_key_last($osv)];
        }
    }
    if(isset($browser)) {
        $result["browser"]["name"] = $browser[0];
        $result["browser"]["version"] = $browser[1];
    }
    $result["is_mobile"] = preg_match('/mobile/i', $user_agent) ? 1 : 0;
    $result["is_bot"] = (preg_match('/bot/i', $user_agent) || preg_match('/crawler/i', $user_agent)) ? 1 : 0;
    return $result;
}

/* Classes */

// WebAnalytics database manager
class web_db_manager {

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
?>