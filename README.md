# b1 web analytics
A ready to use collection of php scripts for web analytics.

## Why should I use b1 web analytics?
There are atleast 4 reasons:
* **it's server-sided**
* **it's independent**
* **it's open source**
* **it's free**

## Usage
Before using the scripts you need to modify the mysql parameters (host, user, password, database) in database.php else none of the scripts will work:

database.php
```php
// Creates database connection
// Modify before use
$b1_analytics_db = new mysqli("localhost", "user", "password", "database");
```

After that all you need to do is including tracker.php into your website to start collecting data:
```php
include "tracker.php";
```

After collecting enough data you will see a simple report when opening statistics.php.

## Requirements
* PHP 5.0 or higher
* a MySQL Server
* (a webserver of course)

### PLEASE NOTE: b1 web tracking is still in development. We rely on your feedback!
