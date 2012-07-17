#retroTrack#

retroTrack is a web-based satellite tracking program that uses TLE's to calculate and display the orbits of satellites using HTML5 canvas. It uses CakePHP to allow administrators to define satellites, satellite groups, ground stations, and tracker configurations.

##Installation##
To install retroTrack, simply:

1. Modify app/Config/database.php.default to reflect your database and save it as 'database.php'
2. Import Development_Resources/SQL_Schema/retrotrack.sql into your newly created database.
3. Modify app/Config/core.php.default by changing the salt (line 187) and seed (line 192) values to something random and save it as 'core.php'.
4. Change the permissions of the app/tmp directory to 755.
5. Generate an admin password hash by visiting retrotrackerlocation.com/admin/panel/makehash.
6. Update the 'admin' user in the database by replacing 'dummypassword' with the hash you just created.

##Using retroTrack##
After retroTrack has been installed, you can access the administration panel at retrotrackerlocation.com/admin using the 'admin' user with the password you created in step 5 above. From the administration panel you can update the TLE's, add satellites, setup satellite groups, add ground stations, and generate static versions of retroTrack (which can run without a server).

