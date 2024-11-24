#!/bin/bash

#Nothing to see here
if [[ -d /passoire/flags-enc ]]; then
	/passoire/.h/b.sh
	/passoire/flags/init.sh
fi

# Updating ownership and permissions of uploads and img folder
chown -R www-data:www-data /passoire/web/uploads && chmod 750 /passoire/web/uploads && chmod 640 /passoire/web/uploads/*
chown -R www-data:www-data /passoire/web/img && chmod 750 /passoire/web/img && chmod 640 /passoire/web/img/*

# Start DB, web server and ssh server
service mysql start
service ssh start
service apache2 start

DB_NAME="passoire"
DB_USER="passoire"
DB_PASSWORD=$(head -n 1 /passoire/config/db_pw)


# Initialize database
echo "Creating MySQL database and user..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
#mysql -u root -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO '${DB_USER}'@'localhost' WITH GRANT OPTION;"
mysql -u root -e "FLUSH PRIVILEGES;"

mysql -u root ${DB_NAME} < config/passoire.sql

#password update for users
mysql -u root -e "UPDATE passoire.users SET pwhash = '\$argon2i\$v=19\$m=65536,t=4,p=1\$YlVOTlVtY3ZjamRDTTB4V2FVRkdlZw\$Pr/BAMLvlCT2/7mgtUl1UoXgZw' WHERE id = 1;"
mysql -u root -e "UPDATE passoire.users SET pwhash = '\$argon2i\$v=19\$m=65536,t=4,p=1\$YlVOTlVtY3ZjamRDTTB4V2FVRkdlZw\$Hg9+2jHJl5UeIKdQ7O71wLNOag' WHERE id = 2;"
mysql -u root -e "UPDATE passoire.users SET pwhash = '\$argon2i\$v=19\$m=65536,t=4,p=1\$YlVOTlVtY3ZjamRDTTB4V2FVRkdlZw\$apaDdIrkUiI7crHkYNeftrKTAw' WHERE id = 4;"

# Updating avatar location for john_doe
mysql -u root -e "UPDATE passoire.userinfos SET avatar = 'img/avatar3.png' WHERE userid = 1;"

# Redirect querry from website root to our main page
rm /var/www/html/index.html
echo "<?php header(\"Location: passoire/index.php\"); ?>" > /var/www/html/index.php

# Link apache dir and our web dir
ln -s /usr/share/phpmyadmin/ /var/www/html/phpmyadmin
ln -s /passoire/web/ /var/www/html/passoire

# Adapt to our ip
echo "127.0.0.1 db" >> /etc/hosts



if [ -z "$HOST" ]; then
  HOST=$(hostname -i)
fi

echo "Web server running at http://$HOST"

sed -i "s/CONTAINER_IP/$HOST/g" /passoire/web/crypto.php
sed -i "s/CONTAINER_IP/$HOST/g" /passoire/crypto-helper/server.js

touch /passoire/logs/crypto-helper.log

# Start crypto helper api
/passoire/crypto-helper/crypto-helper.sh start

# Monitor logs
tail -f /passoire/logs/crypto-helper.log /var/log/apache2/access.log /var/log/apache2/error.log
