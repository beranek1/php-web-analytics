# WebAnalytics
Free open-source web analytics with easy integration into existing PHP scripts.
## Usage
* Place webanalytics.php and websettings.php in the same directory as your own php scripts, and modify the database parameters in websettings.php.
#### OR
* Place only webanalytics.php in the same directory as your own php scripts and modify the database parameters in webanalytics.php.

websettings.php / webanalytics.php
```php
$web_analytics_db = new web_db_manager("mysql:dbname=database;host=127.0.0.1", "user", "password");
```

After that all you need to do is including webanalytics.php in your own php scripts to start collecting data:
```php
include "webanalytics.php";
```

After collecting enough data you will see a simple report when opening webstatistics.php.

### Interested in using WebAnalytics as a library or in a modified way? You can disable auto run in the settings:
```php
$web_auto_run = FALSE;
```

To run web analytics manually use following code:
```php
$web_analytics_db->connect();
$web_analytics = new web_analytics($web_analytics_db, $_SERVER, $_COOKIE);
```

As a professional you might also want to try our yet experimental JavaScript for collecting additional data:
```html
<script src="wa.js"></script>
```

## Requirements
* PHP 5.0 or higher
* a database server with PDO driver
* (a webserver of course)

## Affiliated projects
* [GeoIP / IP Geo Location](https://geoip.beranek.one)
* [UAA / User Agent Analyser](https://uaa.beranek.one)

## Frameworks / Libraries used for dashboard
* [Bootstrap](https://getbootstrap.com)
* [Google Charts](https://developers.google.com/chart/)

# Information about upcoming releases
## 0.3 new API
This version will include a new universal php script called "wa.php" that can be used for both a library for collecting and analysing data.
## 0.4 new page for viewing statistics based on the API
## 0.5 merging all classes to one
## 1.0
