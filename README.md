Intrument Reservation System
-------------------------
This PHP project is an reservation system. Said instrument could presumably be a photographic camera, for example.
There are 2 types of users. An administrator or the one who controls the overall system and standard users who
rent the instruments for some specific time. It's fool-proof, to a degree.

Currently live at: https://instrument-reservation.herokuapp.com/

Getting Started
------------------
This program was created under Windows 7 (x64) Operative System using PHP 5.6, Apache 2.4, Composer 1.10, MySQL 5.7, HTML5, W3C and jQuery.

Prerequisites
---------------
I would advise only using official installers.

To install PHP:
https://www.php.net/manual/en/install.windows.manual.php

To install Apache server:
https://httpd.apache.org/download.cgi

To install Composer:
https://getcomposer.org/doc/00-intro.md

Also MySQL 5.7 is required:
https://dev.mysql.com/downloads/installer/

Deployment
--------------
Before starting. Please rename the file '.env.default' to '.env', and set in it your MySQL credentials.
Then you need to edit the config.php file, and remove the long comments '/*' and '*/'. NOT what's inside of them!

1 - In order to link all composer dependencies:

    ./> composer i
    [Writing lock file]
	[Generating autoload files]

2 - Run your Apache server.

3- Then opening the (presumably) website on **Google Chrome**(*):

  localhost/Instrument-reservation-system/

Files
------
All files in this repository should be self explanatory.
The most relevant files are:

web/config.php:
	holds the database configuration. Before running, please alter the database data here.

web/calendar.php:
	contains the graphical (and clickable) calendar. Pretty cool.

web/success.php:
	responsable for the back-end of the entire application. It re-routs, verifies inputs, database queries and more.

Versioning
------------
Version 1.6 - Current version

Version 2.5(TBA) - Payment feature

Author
---------
Lucio Afonso

License
---------
This project is licensed under the GPL License - see the LICENSE.md file for details
