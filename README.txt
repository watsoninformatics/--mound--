Created by Josh Watson moundlabs.com

Project:  ::mound:: v2.1.6 STABLE

Description:  The goal of this project is to create an software development management application that is quick and easy to
use.  It should provide utilities for project evaluation, assigning, tracking and collaboration.

Note:  I hope you find this app useful.  I don't want any money, but it would be great if you could contribute to this
project.  If you found this project useful, please email me at jmwtampa@gmail.com and let me know.  

Thanks:  Thanks to Justin Vincent for the ez_sql class, Colin Verot for the upload class and everyone who contributes to the 
smarty template engine. Also, a thanks to CSS3 Menu for making the menu much easier to code and HIOX Softwares (http://www.hscripts.com)
for the stopwatch code.

License:  Released under GPLv3.  That means it is free and as such I offer no warranty, but if you find any bugs I would like
to know about them.


----------------------------------------------------------------------------------------------
Install:

1.  Create a mysql database on your server.  Use phpmadmin and import the script in the sql folder.  
This will create the basic database.

2.  Upload the php folder and rename to mound or something meaningful to you.  Doesn't really matter what.

3.  Edit config/config.php.  Set the database conenction info, the salt (this is the string used for uniquely 
encrypting passwords), the admin login info and the weighting factors for determining project value.  
You can also change the attachments folder, which is where uploaded attachments go.

4.  Set the security level on your attachments, templates_c and cache folder to allow for reading and writing (chmod 775).
Also, be sure to turn off autoindex of the attachments folder.

5.  Go to admin.php.  Login with the admin login from your config file and create some users.  By default, there is a user of test with a password of test.
Be sure to delete this user from the user table when you release this page or you can just change the salt in the config file,
which will make test's password useless.  
Then go to index.php and start using.  If you find some bugs or have some suggestions, email me at jmwtampa@gmail.com.

OR -----------------------

You can make this super easy on yourself and just use Softaculous.

---------------------------------------------------------------------------------------------
Upgrading from 1.5 Stable or later:

Delete everything except for the config and attachments folders.  Then copy everything from the php folder except for the config and attachments folders.  
Reset permissions and you should be good to go.