#!/bin/bash

#Nothing to see here
if [[ -d /passoire/flags-enc ]]; then
	/passoire/.h/b.sh
	/passoire/flags/init.sh
fi

# Adding system user with minimal permissions for apps that don't require permissions
useradd --system --no-create-home --shell /usr/sbin/nologin normal-user

# Changing ownership and permissions of crypto-helper server to normal-user
chown normal-user /passoire/crypto-helper
chown -R normal-user /passoire/crypto-helper/{node_modules,server.js}
chmod 750 /passoire/crypto-helper/*

# Updating ownership and permissions of uploads and img folder
chown -R www-data:www-data /passoire/web/uploads && chmod 640 /passoire/web/uploads/*
chmod 640 /passoire/web/img/*

# Updating permissions of passoire user home directory
chown -R passoire /home/passoire && chmod 750 /home/passoire && chmod 640 /home/passoire/*

# Removing admin user from server
userdel -r admin

# Removing removable flags
rm /passoire/my_own_cryptographic_algorithm
rm /passoire/web/flag_3
mv /passoire/web/uploads/encryptedFile /passoire/web/uploads/encrypted

# Start DB, web server and ssh server
service mysql start
service ssh start
service apache2 start

DB_NAME="passoire"
DB_USER="passoire"
DB_PASSWORD=$(head -n 1 /passoire/config/db_pw)

# Adapt to our ip
echo "127.0.0.1 db" >> /etc/hosts

if [ -f "/passoire/logs/initialized" ]; then
	echo "Initialization has already been performed"
else
	# Initialize database
	echo "Creating MySQL database and user..."
	mysql -u root -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
	mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
	#mysql -u root -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
	mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO '${DB_USER}'@'localhost' WITH GRANT OPTION;"
	mysql -u root -e "FLUSH PRIVILEGES;"

	mysql -u root ${DB_NAME} < config/passoire.sql

	# Password update for users
	mysql -u root -e "UPDATE passoire.users SET pwhash = '\$argon2i\$v=19\$m=65536,t=4,p=1\$czdSUHFtanFTTnlGdUMxRA\$X+rAIVERceWDTVR1ywjsdLwRjA' WHERE id = 1;"
	mysql -u root -e "UPDATE passoire.users SET pwhash = '\$argon2i\$v=19\$m=65536,t=4,p=1\$eGtFYnZrRGFQc3RLT0tKNw\$o7qnNf5aZXO4EnAoB78jr8ksdw' WHERE id = 2;"

	# Removing admin user from DB
	mysql -u root -e "DELETE FROM passoire.users WHERE id = 4;"

	# Updating avatar location for john_doe
	mysql -u root -e "UPDATE passoire.userinfos SET avatar = 'img/avatar3.png' WHERE userid = 1;"

	# Redirect querry from website root to our main page
	rm /var/www/html/index.html
	echo "<?php header(\"Location: passoire/index.php\"); ?>" > /var/www/html/index.php

	# Link apache dir and our web dir
	ln -s /passoire/web/ /var/www/html/passoire

	touch /passoire/logs/initialized
fi


if [ -z "${HOST+x}" ] || [ -z "${HOST}" ]; then
  HOST=$(hostname -i)
fi

echo "Web server running at http://$HOST"

# Adapt to HOST variable regardless of past modifications.
sed -E -i "s/^const host = \"(CONTAINER_IP|localhost|[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)\";$/const host = \"$HOST\";/" /passoire/crypto-helper/server.js
sed -E -i "s@http://(CONTAINER_IP|localhost|[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+):3002@http://$HOST:3002@g" /passoire/web/crypto.php

# Fix bug with DES encryption/decryption
sed -i 's/ -provider legacy -provider default//g' /passoire/crypto-helper/server.js

touch /passoire/logs/crypto-helper.log
chown -R normal-user /passoire/logs

# Start crypto helper api
/passoire/crypto-helper/crypto-helper.sh start

# Monitor logs
tail -f /passoire/logs/crypto-helper.log /var/log/apache2/access.log /var/log/apache2/error.log
