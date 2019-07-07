# WebAnalytics
A ready to use collection of php scripts for web analytics.

## Why should I use WebAnalytics?
There are atleast 4 reasons:
* **it's server-sided**
* **it's independent**
* **it's open source**
* **it's free**

## Usage
* Place webanalytics.php and websettings.php in the same directory as your own php scripts, and modify the mysql parameters (user, password, database, host) in websettings.php. (Recommended, makes updates easier)
#### OR
* Place only webanalytics in the same directory as your own php scripts and modify the mysql parameters (user, password, database, host) in webanalytics.php.

websettings.php / webanalytics.php
```php
$web_analytics_db = new web_db_manager("user", "password", "database", "localhost");
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
$web_analytics_db->close();
```

As a professional you might also want to try our yet experimental JavaScript for collecting additional data, following will output a html <script> element containing the script:
```php
$web_analytics->echo_script();
```

## Requirements
* PHP 5.0 or higher
* a MySQL Server
* (a webserver of course)

### PLEASE NOTE: WebAnalytics is still in development. We rely on your feedback!
