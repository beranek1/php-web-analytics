<?php
/*
#-----------------------------------------
| b1 web analytics: tracker
| https://beranek1.github.io/webanalytics/
#-----------------------------------------
| Include this file in your php sites
| as shown in example.php
#-----------------------------------------
| made by beranek1
| https://github.com/beranek1
#-----------------------------------------
*/

include "./configs/database.php";
include "./libraries/analytics.php";

// Runs analytics
$b1_analytics = new b1_analytics($b1_analytics_db, $_SERVER, $_COOKIE);

// Closes database connection
$b1_analytics_db->close();
?>