# b1 web analytics
A ready to use collection of php scripts for web analytics.

b1 analytics is a yet unfinished project based on php scripts I made in my past for analysing web traffic, feel free to leave feedback and use them for your own projects. Currently I have no concrete plans on finishing or continuing the project, but I'll use the time I have to fix bugs and keep it working.

analyser.php and statistics.php are just experimental yet, but shall work.

## Why should I use b1 web analytics?
There are atleast 4 reasons:
* **it's server-sided**
* **it's independent**
* **it's open source**
* **it's free**

## Scripts
### analytics.php
The main script necessary for collecting and saving data, setting cookies, creating tables and identifying the visitors.
### database.php
Creates the database connection and holds settings
### example.php
An usage example, that outputs a script for additional tracking using echoscript()
### *analyser.php*
Holds functions for creating statistics, unfinished
### *statistics.php*
Simple website that shows statistics based on the collected data

## Usage
Before using the scripts you need to modify the mysql parameters (host, user, password, database) in database.php else none of the scripts will work.

Use example.php as sample code for integrating analytics.php into your own scripts.
($b1_analytics->echoscript() is not necessary and might breaks the design of your website if used.)

### Always remember to inform your users about data collection in your privacy policy!
