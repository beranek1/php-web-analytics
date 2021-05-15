# WebAnalytics
Free open-source web analytics with easy integration into existing PHP scripts.
## Usage
* Place webanalytics.php and websettings.php in the same directory as your own php scripts, and modify the database parameters in websettings.php.
#### OR
* Place only webanalytics.php in the same directory as your own php scripts and modify the database parameters in webanalytics.php as well as webstatistics.php.

websettings.php / webanalytics.php / webstatistics.php
```php
$web_analytics_db = new web_db_manager("mysql:dbname=database;host=127.0.0.1", "user", "password");
```

Afterwards all you need to do is including webanalytics.php in your own php scripts to start collecting data:
```php
include "webanalytics.php";
```

Run your script / webanalytics.php once to initialize the database tables, now webstatistics.php will show you an current analysis of your web traffic and visitors.

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
* [IPA / IP Anonymizer](https://github.com/beranek1/php-ip-anonymizer)

## Frameworks / Libraries used for dashboard
* [Bootstrap](https://getbootstrap.com)
* [Google Charts](https://developers.google.com/chart/)

# Information about upcoming releases
I've decided to stop implementing new features myself as the possible use cases for this project are very limited. However contributions are still very welcome.
Feel free to implement new features yourself and submit a merge-request so everyone can benefit from your changes.

I'll (probably) later this year introduce new projects that focus on web analytics solutions for cloud environments like AWS, Tencent Cloud and Alibaba Cloud.
