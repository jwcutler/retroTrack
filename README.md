#retroTrack#

retroTrack is a web-based satellite tracking program that uses TLE's to calculate and display the orbits of satellites using HTML5 canvas.

Dependencies
------------
retroTrack was developed on top of the popular CakePHP MVC framework and shares its requirements. Namely:
* An HTTP server (e.g. Apache)
* PHP >= 5.2.8  (note, php7 is not yet usable, stick with 5.*)
* MySQL >= 4

In addition, the retroTracker satellite tracker display requires that the user's browser supports:
* Javascript
* HTML5 Canvas

Finally, cURL must be installed and loaded into PHP.

Installation
------------
To install retroTrack, perform the following configurations:

0. Create a database in MySql for use by retrotrack.  Keep track of db name, user, and password.  Database tables will be created in step 2.
1. Modify www/app/Config/database.php.default to reflect your database and save it as 'database.php'
2. Import Development_Resources/SQL_Schema/retrotrack.sql into your newly created database.
3. Modify app/Config/core.php.default by changing Security.salt (line 187) and Security.cipherseed (line 192) to random values specific to your application and save it as 'core.php'.
4. Change the permissions of the app/tmp directory to 777.
5. Generate an admin password hash by visiting retrotrackerlocation.com/admin/panel/makehash.
6. Update the 'admin' user in the database by replacing 'dummypassword' with the hash you just created.

### Installing retroTrack to a directory
If you want to install retroTrack to a directory (or use Apache's Alias feature), you must add a rewrite base for the directory to three .htaccess files. These files are:
* /path/to/retroTrack/.htaccess
* /path/to/retroTrack/app/.htaccess
* /path/to/retroTrack/app/webroot/.htaccess

So, for example, if retroTrack were setup at mysite.com/retrotrack/ webroot/.htaccess would look like:
```
<IfModule mod_rewrite.c>
    RewriteEngine On
	RewriteBase /retrotrack/
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

To configure Apache to run retroTrack from a directory, simply setup an alias and enable FollowSymLinks (important) for that directory. For example, if you want retroTrack to appear at /tools/retrotrack, your default virtual host may look like:
```
<VirtualHost *:80>
    DocumentRoot /var/www
    DirectoryIndex index.html index.php
    
    # Setup directory aliases
    Alias /tools/retrotrack /var/other_apps/retroTrack/www/app/webroot
    <Directory /var/other_apps/retroTrack/www/app/webroot>
	Options FollowSymLinks
	AllowOverride ALL
    </Directory>
</VirtualHost>
```
This is preferred to just copying retroTrack to a directory of /var/www because it only allows access to the app/webroot folder. 

Using retroTrack
----------------
After retroTrack has been installed, you can access the administration panel at retrotrackerlocation.com/admin using the 'admin' user with the password you created in step 5 above. From the administration panel you can:
* Manually update TLE's
* Manage visible satellites
* Manage satellite groups
* Manage ground stations
* Configure appearance of satellite tracker
* Generate static, client-side, versions of satellite trackers

### Modifying retroTrack
If you wish to modify retroTrack tracker display page, remember to also update the static version template in app/Vendor/static_template/index.html.

### CRON Updates
retroTracks's TLE source can be updated by using a standard CRON tab. To make use of this feature, simply set up a CRON tab to call:

```
/path/to/retroTrack/app/Console/cake TleUpdate update
```

Credits
-------
The retroTrack satellite tracker relies heavily on [John A. Magliacane's javascript port](https://bitbucket.org/andrewtwest/orbtrak) of [Predict](http://www.qsl.net/kd2bd/predict.html).

