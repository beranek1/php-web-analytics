<?php
/*
#-----------------------------------------
| b1 web analytics: statistics viewer
| https://beranek1.github.io/webanalytics/
#-----------------------------------------
| made by beranek1
| https://github.com/beranek1
#-----------------------------------------
*/

$country_to_continent = array ("AD"=>"EU","AE"=>"AS","AF"=>"AS","AG"=>"NA","AI"=>"NA","AL"=>"EU","AM"=>"AS","AN"=>"NA","AO"=>"AF","AP"=>"AS","AR"=>"SA","AS"=>"OC","AT"=>"EU","AU"=>"OC","AW"=>"NA","AX"=>"EU","AZ"=>"AS","BA"=>"EU","BB"=>"NA","BD"=>"AS","BE"=>"EU","BF"=>"AF","BG"=>"EU","BH"=>"AS","BI"=>"AF","BJ"=>"AF","BL"=>"NA","BM"=>"NA","BN"=>"AS","BO"=>"SA","BR"=>"SA","BS"=>"NA","BT"=>"AS","BV"=>"AN","BW"=>"AF","BY"=>"EU","BZ"=>"NA","CA"=>"NA","CC"=>"AS","CD"=>"AF","CF"=>"AF","CG"=>"AF","CH"=>"EU","CI"=>"AF","CK"=>"OC","CL"=>"SA","CM"=>"AF","CN"=>"AS","CO"=>"SA","CR,NA","CU"=>"NA","CV"=>"AF","CX"=>"AS","CY"=>"AS","CZ"=>"EU","DE"=>"EU","DJ"=>"AF","DK"=>"EU","DM"=>"NA","DO"=>"NA","DZ"=>"AF","EC"=>"SA","EE"=>"EU","EG"=>"AF","EH"=>"AF","ER"=>"AF","ES"=>"EU","ET"=>"AF","EU"=>"EU","FI"=>"EU","FJ"=>"OC","FK"=>"SA","FM"=>"OC","FO"=>"EU","FR"=>"EU","FX"=>"EU","GA"=>"AF","GB"=>"EU","GD"=>"NA","GE"=>"AS","GF"=>"SA","GG"=>"EU","GH"=>"AF","GI"=>"EU","GL"=>"NA","GM"=>"AF","GN"=>"AF","GP"=>"NA","GQ"=>"AF","GR"=>"EU","GS"=>"AN","GT"=>"NA","GU"=>"OC","GW"=>"AF","GY"=>"SA","HK"=>"AS","HM"=>"AN","HN"=>"NA","HR"=>"EU","HT"=>"NA","HU"=>"EU","ID"=>"AS","IE"=>"EU","IL"=>"AS","IM"=>"EU","IN"=>"AS","IO"=>"AS","IQ"=>"AS","IR"=>"AS","IS"=>"EU","IT"=>"EU","JE"=>"EU","JM"=>"NA","JO"=>"AS","JP"=>"AS","KE"=>"AF","KG"=>"AS","KH"=>"AS","KI"=>"OC","KM"=>"AF","KN"=>"NA","KP"=>"AS","KR"=>"AS","KW"=>"AS","KY"=>"NA","KZ"=>"AS","LA"=>"AS","LB"=>"AS","LC"=>"NA","LI"=>"EU","LK"=>"AS","LR"=>"AF","LS"=>"AF","LT"=>"EU","LU"=>"EU","LV"=>"EU","LY"=>"AF","MA"=>"AF","MC"=>"EU","MD"=>"EU","ME"=>"EU","MF"=>"NA","MG"=>"AF","MH"=>"OC","MK"=>"EU","ML"=>"AF","MM"=>"AS","MN"=>"AS","MO"=>"AS","MP"=>"OC","MQ"=>"NA","MR"=>"AF","MS"=>"NA","MT"=>"EU","MU"=>"AF","MV"=>"AS","MW"=>"AF","MX"=>"NA","MY"=>"AS","MZ"=>"AF","NA"=>"AF","NC"=>"OC","NE"=>"AF","NF"=>"OC","NG"=>"AF","NI"=>"NA","NL"=>"EU","NO"=>"EU","NP"=>"AS","NR"=>"OC","NU"=>"OC","NZ"=>"OC","OM"=>"AS","PA"=>"NA","PE"=>"SA","PF"=>"OC","PG"=>"OC","PH"=>"AS","PK"=>"AS","PL"=>"EU","PM"=>"NA","PN"=>"OC","PR"=>"NA","PS"=>"AS","PT"=>"EU","PW"=>"OC","PY"=>"SA","QA"=>"AS","RE"=>"AF","RO"=>"EU","RS"=>"EU","RU"=>"EU","RW"=>"AF","SA"=>"AS","SB"=>"OC","SC"=>"AF","SD"=>"AF","SE"=>"EU","SG"=>"AS","SH"=>"AF","SI"=>"EU","SJ"=>"EU","SK"=>"EU","SL"=>"AF","SM"=>"EU","SN"=>"AF","SO"=>"AF","SR"=>"SA","ST"=>"AF","SV"=>"NA","SY"=>"AS","SZ"=>"AF","TC"=>"NA","TD"=>"AF","TF"=>"AN","TG"=>"AF","TH"=>"AS","TJ"=>"AS","TK"=>"OC","TL"=>"AS","TM"=>"AS","TN"=>"AF","TO"=>"OC","TR"=>"EU","TT"=>"NA","TV"=>"OC","TW"=>"AS","TZ"=>"AF","UA"=>"EU","UG"=>"AF","UM"=>"OC","US"=>"NA","UY"=>"SA","UZ"=>"AS","VA"=>"EU","VC"=>"NA","VE"=>"SA","VG"=>"NA","VI"=>"NA","VN"=>"AS","VU"=>"OC","WF"=>"OC","WS"=>"OC","YE"=>"AS","YT"=>"AF","ZA"=>"AF","ZM"=>"AF","ZW"=>"AF");
include "b1settings.php";

$ttlrqtsr = $b1_analytics_db->get_one_row("SELECT COUNT(*) FROM requests;");
$total_requests = $ttlrqtsr[0];
if($total_requests == 0) {
    echo "Not enough data collected yet.<br>";
    echo "<a href=\"https://beranek1.github.io/webanalytics/\">b1 web analytics</a>";
    $b1_analytics_db->close();
    return;
}
$ttlvstrsr = $b1_analytics_db->get_one_row("SELECT COUNT(*) FROM browsers;");
$total_visitors = $ttlvstrsr[0];
$ttlntwsr = $b1_analytics_db->get_one_row("SELECT COUNT(*) FROM networks;");
$total_networks = $ttlntwsr[0];
$ttlispsr = $b1_analytics_db->get_one_row("SELECT COUNT(*) FROM isps;");
$total_isps = $ttlispsr[0];
$mstrqstsr = $b1_analytics_db->get_rows_array("SELECT `visitor_country`, COUNT(*) FROM requests GROUP BY `visitor_country` ORDER BY COUNT(*) DESC;");
$top_countries = array();
$top_continents = array();
$total_continents = 0;
foreach($mstrqstsr as $country) {
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
$tpvstrsor = $b1_analytics_db->get_rows_array("SELECT `country`, COUNT(*) FROM browsers GROUP BY `country` ORDER BY COUNT(*) DESC;");
$top_countriesvo = array();
foreach($tpvstrsor as $country) {
    if($country[0] != "" && $country[0] != null) {
        $top_countriesvo[$country[0]] = $country[1];
        $total_countries = $total_countries + 1;
    } else {
        $top_countriesvo["?"] = $country[1];
    }
}
$tplngsr = $b1_analytics_db->get_rows_array("SELECT `language`, COUNT(*) FROM browsers GROUP BY `language` ORDER BY COUNT(*) DESC;");
$top_languages = array();
$total_languages = 0;
foreach($tplngsr as $language) {
    if($language != "" && $language != null) {
        $top_languages[$language[0]] = $language[1];
        $total_languages = $total_languages + 1;
    } else {
        $top_languages["?"] = $language[1];
    }
}
$tpusragntsr = $b1_analytics_db->get_rows_array("SELECT `agent_id`, COUNT(*) FROM browsers GROUP BY `agent_id` ORDER BY COUNT(*) DESC;");
$top_useragents = array();
foreach($tpusragntsr as $useragent) {
    $top_useragents[$useragent[0]] = $useragent[1];
}
$tpispsr = $b1_analytics_db->get_rows_array("SELECT `isp_id`, COUNT(*) FROM networks GROUP BY `isp_id` ORDER BY COUNT(*) DESC;");
$top_isps = array();
foreach($tpispsr as $isp) {
    $top_isps[$isp[0]] = $isp[1];
}
$tpurir = $b1_analytics_db->get_rows_array("SELECT `uri`, COUNT(*) FROM requests GROUP BY `uri` ORDER BY COUNT(*) DESC;");
$top_uris = array();
foreach($tpurir as $uri) {
    $top_uris[$uri[0]] = $uri[1];
}
?>
<html>
<head>
<meta name="robots" content="noindex,nofollow">
<title>b1 web analytics</title>
<style>
body {
    font-family: arial, sans-serif;
    max-width: 100%;
}
table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 100%;
}
td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
}
tr:nth-child(even) {
    background-color: #dddddd;
}
progress {
    width: 100%;
}
@media only screen and (min-width: 600px) {
    div {
        width: 48%; 
        float: left;
        padding:1%;
    }
}
</style>
</head>
<body>
    <h1>Website statistics</h1>
    <div>
    <h2>All time statistics</h2>
    <table>
    <tr><td>Total requests</td><td><?php echo $total_requests; ?></td></tr>
    <tr><td>Total visitors</td><td><?php echo $total_visitors; ?></td></tr>
    <tr><td>Total networks</td><td><?php echo $total_networks; ?></td></tr>
    <tr><td>Total ISPs</td><td><?php echo $total_isps; ?></td></tr>
    <tr><td>Total countries</td><td><?php echo $total_countries; ?></td></tr>
    <tr><td>Total continents</td><td><?php echo $total_continents; ?></td></tr>
    <tr><td>Total languages</td><td><?php echo $total_languages; ?></td></tr>
    </table>
    </div>
    <div>
    <h2>Average visitor</h2>
    <table>
    <thead>
    <tr><th></th><th></th><th>proportion</th><th>chart</th></tr>
    </thead>
    <tr><td>Requests</td><td><?php echo "".round(($total_requests/$total_visitors), 2).""; ?></td></tr>
    <tr><td>Country</td><td><?php echo "".array_keys($top_countriesvo)[0].""; ?></td><td><?php echo "".round((array_values($top_countriesvo)[0] / $total_visitors)*100, 2).""; ?>%</td><td><progress value="<?php echo "".((array_values($top_countriesvo)[0] / $total_visitors)*100).""; ?>" max="100"></progress></td></tr>
    <tr><td>Language</td><td><?php echo "".array_keys($top_languages)[0].""; ?></td><td><?php echo "".round((array_values($top_languages)[0] / $total_visitors)*100, 2).""; ?>%</td><td><progress value="<?php echo "".((array_values($top_languages)[0] / $total_visitors)*100).""; ?>" max="100"></progress></td></tr>
    <tr><td>User agent</td><td><?php echo "".array_keys($top_useragents)[0].""; ?></td><td><?php echo "".round((array_values($top_useragents)[0] / $total_visitors)*100, 2).""; ?>%</td><td><progress value="<?php echo "".((array_values($top_useragents)[0] / $total_visitors)*100).""; ?>" max="100"></progress></td></tr>
    <tr><td>ISP</td><td><?php echo "".array_keys($top_isps)[0].""; ?></td><td><?php echo "".round((array_values($top_isps)[0] / $total_visitors)*100, 2).""; ?>%</td><td><progress value="<?php echo "".((array_values($top_isps)[0] / $total_visitors)*100).""; ?>" max="100"></progress></td></tr>
    </table>
    </div>
    <div>
    <h2>Countries ordered by requests</h2>
    <table>
    <thead>
    <tr><th>Country code</th><th>requests</th><th>proportion</th><th>chart</th></tr>
    </thead>
    <?php foreach ($top_countries as $key => $value) { echo "<tr><td>".$key."</td><td>".$value."</td><td>".round(($value/$total_requests)*100, 2)."%</td><td><progress value='".(($value/$total_requests)*100)."' max='100'></progress></td></tr>"; } ?>
    <tr><th>Total</th><th><?php echo $total_requests; ?></th></tr>
    </table>
    </div>
    <div>
    <h2>Countries ordered by visitors</h2>
    <table>
    <thead>
    <tr><th>Country code</th><th>visitors</th><th>proportion</th><th>chart</th></tr>
    </thead>
    <?php foreach ($top_countriesvo as $key => $value) { echo "<tr><td>".$key."</td><td>".$value."</td><td>".round(($value/$total_visitors)*100, 2)."%</td><td><progress value='".(($value/$total_visitors)*100)."' max='100'></progress></td></tr>"; } ?>
    <tr><th>Total</th><th><?php echo $total_visitors; ?></th></tr>
    </table>
    </div>
    <div>
    <h2>Continents ordered by requests</h2>
    <table>
    <thead>
    <tr><th>Continent code</th><th>requests</th><th>proportion</th><th>chart</th></tr>
    </thead>
    <?php foreach ($top_continents as $key => $value) { echo "<tr><td>".$key."</td><td>".$value."</td><td>".round(($value/$total_requests)*100, 2)."%</td><td><progress value='".(($value/$total_requests)*100)."' max='100'></progress></td></tr>"; } ?>
    <tr><th>Total</th><th><?php echo $total_requests; ?></th></tr>
    </table>
    </div>
    <div>
    <h2>Languages ordered by visitors</h2>
    <table>
    <thead>
    <tr><th>Language</th><th>visitors</th><th>proportion</th><th>chart</th></tr>
    </thead>
    <?php foreach ($top_languages as $key => $value) { echo "<tr><td>".$key."</td><td>".$value."</td><td>".round(($value/$total_visitors)*100, 2)."%</td><td><progress value='".(($value/$total_visitors)*100)."' max='100'></progress></td></tr>"; } ?>
    <tr><th>Total</th><th><?php echo $total_visitors; ?></th></tr>
    </table>
    </div>
    <div>
    <h2>Top user agents ordered by users</h2>
    <table>
    <thead>
    <tr><th>Agent ID</th><th>users</th><th>proportion</th><th>chart</th></tr>
    </thead>
    <?php foreach ($top_useragents as $key => $value) { echo "<tr><td>".$key."</td><td>".$value."</td><td>".round(($value/$total_visitors)*100, 2)."%</td><td><progress value='".(($value/$total_visitors)*100)."' max='100'></progress></td></tr>"; } ?>
    <tr><th>Total</th><th><?php echo $total_visitors; ?></th></tr>
    </table>
    </div>
    <div>
    <h2>Top isps ordered by networks</h2>
    <table>
    <thead>
    <tr><th>ISP ID</th><th>networks</th><th>proportion</th><th>chart</th></tr>
    </thead>
    <?php foreach ($top_isps as $key => $value) { echo "<tr><td>".$key."</td><td>".$value."</td><td>".round(($value/$total_networks)*100, 2)."%</td><td><progress value='".(($value/$total_networks)*100)."' max='100'></progress></td></tr>"; } ?>
    <tr><th>Total</th><th><?php echo $total_isps; ?></th></tr>
    </table>
    </div>
    <div>
    <h2>URIs/Pages ordered by requests</h2>
    <table>
    <thead>
    <tr><th>URI</th><th>requests</th><th>proportion</th><th>chart</th></tr>
    </thead>
    <?php foreach ($top_uris as $key => $value) { echo "<tr><td>".$key."</td><td>".$value."</td><td>".round(($value/$total_requests)*100, 2)."%</td><td><progress value='".(($value/$total_requests)*100)."' max='100'></progress></td></tr>"; } ?>
    <tr><th>Total</th><th><?php echo $total_requests; ?></th></tr>
    </table>
    </div>
</body>
<footer>
    <a href="https://beranek1.github.io/webanalytics/">Powered by b1 web analytics</a>
</footer>
</html>
<?php
$b1_analytics_db->close();
?>