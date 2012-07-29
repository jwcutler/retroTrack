#retroTrack#

retroTrack is a web-based satellite tracking program that uses TLE's to calculate and display the orbits of satellites using HTML5 canvas.

Dependencies
------------
retroTrack was developed on top of the popular CakePHP MVC framework and shares its requirements. Namely:
* An HTTP server (e.g. Apache)
* PHP >= 5.2.8
* MySQL >= 4

In addition, the retroTracker satellite tracker display requires that the user's browser supports:
* Javascript
* HTML5 Canvas

Installation
------------
To install retroTrack, perform the following configurations:

1. Modify app/Config/database.php.default to reflect your database and save it as 'database.php'
2. Import Development_Resources/SQL_Schema/retrotrack.sql into your newly created database.
3. Modify app/Config/core.php.default by changing the salt (line 187) and seed (line 192) to random values specific to your application and save it as 'core.php'.
4. Change the permissions of the app/tmp directory to 755.
5. Generate an admin password hash by visiting retrotrackerlocation.com/admin/panel/makehash.
6. Update the 'admin' user in the database by replacing 'dummypassword' with the hash you just created.

``` php
/**
 * A random string used in security hashing methods.
 */
Configure::write('Security.salt', 'yoursaltvalue');

/**
 * A random numeric string (digits only) used to encrypt/decrypt strings.
 */
Configure::write('Security.cipherSeed', 'yourcipherseedvalue');
```

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

Credits
-------
The retroTrack satellite tracker relies heavily on [John A. Magliacane's javascript port](https://bitbucket.org/andrewtwest/orbtrak) of [Predict](http://www.qsl.net/kd2bd/predict.html).

