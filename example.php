<?php
include "database.php";
include "analytics.php";

// Runs analytics
$b1_analytics = new b1_analytics($b1_analytics_db, $_SERVER, $_COOKIE);

// Closes database connection
$b1_analytics_db->close();

// Outputs javascript for additional tracking
$b1_analytics->echoscript();
?>