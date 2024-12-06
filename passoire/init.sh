#!/bin/bash

#Nothing to see here
if [[ -d /passoire/flags-enc ]]; then
	/passoire/.h/b.sh
	/passoire/flags/init.sh
fi

# Start DB, web server and ssh server
service mysql start
service ssh start
service apache2 start

# Adapt to our ip
echo "127.0.0.1 db" >> /etc/hosts

if [ -f "/passoire/logs/initialized" ]; then
	echo "Initialization has already been performed"
else
	# Restricting permissions on secret files
	chmod 600 /passoire/config/db_pw_new /passoire/config/passoire.sql

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

	# Updating permissions of /etc/environment
	chmod 640 /etc/environment

	# Removing admin user from server
	userdel -r admin

	# Removing removable flags
	rm /passoire/my_own_cryptographic_algorithm
	rm /passoire/web/flag_3
	mv /passoire/web/uploads/encryptedFile /passoire/web/uploads/encrypted
	rm /passoire/logs/init.log


	mv /passoire/config/db_pw_new /passoire/config/db_pw

	DB_NAME="passoire"
	DB_USER="passoire"
	DB_PASSWORD=$(head -n 1 /passoire/config/db_pw)
	ROOT_PASSWORD=$(tail -n 1 /passoire/config/db_pw)

	rm /passoire/config/db_pw

	echo "export DB_PASS=$DB_PASSWORD" >> /etc/environment
	echo ". /etc/environment" >> /etc/apache2/envvars

	# Initialize database
	echo "Creating MySQL database and user..."
	mysql -u root -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
	mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
	mysql -u root -e "GRANT INSERT, SELECT, UPDATE, DELETE ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"

	mysql -u root ${DB_NAME} < config/passoire.sql

	rm /passoire/config/passoire.sql

	# Enabling encryption at rest for passoire database
	mysql -u root -e "ALTER TABLE passoire.users ENCRYPTION='Y';"
	mysql -u root -e "ALTER TABLE passoire.files ENCRYPTION='Y';"
	mysql -u root -e "ALTER TABLE passoire.messages ENCRYPTION='Y';"
	mysql -u root -e "ALTER TABLE passoire.userinfos ENCRYPTION='Y';"
	mysql -u root -e "ALTER TABLE passoire.links ENCRYPTION='Y';"

	# Password update for users
	mysql -u root -e "UPDATE passoire.users SET pwhash = '\$argon2i\$v=19\$m=65536,t=4,p=1\$czdSUHFtanFTTnlGdUMxRA\$X+rAIVERceWDTVR1ywjsdLwRjA' WHERE id = 1;"
	mysql -u root -e "UPDATE passoire.users SET pwhash = '\$argon2i\$v=19\$m=65536,t=4,p=1\$eGtFYnZrRGFQc3RLT0tKNw\$o7qnNf5aZXO4EnAoB78jr8ksdw' WHERE id = 2;"
	
	#Update existing file links
	mysql -u root -e "UPDATE passoire.links SET hash = '30590d2d421dff125e2d4345d39ed36984280c65' WHERE fileid = 1;"
	mysql -u root -e "UPDATE passoire.links SET hash = 'fca2bf7855934e69936b49acb9a45fa4a3dcb9d5' WHERE fileid = 2;"
	mysql -u root -e "UPDATE passoire.links SET hash = 'e73f35f8c38dc38109d0a16e99d015969a8cfe89' WHERE fileid = 3;"
	mysql -u root -e "UPDATE passoire.messages SET content = 'Here is a file for you, it\'s encrypted with an algorithm of my own invention. I sent you the key by text message. Here is the <a href=\"link.php?file=fca2bf7855934e69936b49acb9a45fa4a3dcb9d5\">link </a>.' WHERE id = 45;"	

	# Removing admin user from DB
	mysql -u root -e "DELETE FROM passoire.users WHERE id = 4;"

	# Updating avatar location for john_doe
	mysql -u root -e "UPDATE passoire.userinfos SET avatar = 'img/avatar3.png' WHERE userid = 1;"

	# Updating root password
	mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH 'caching_sha2_password' BY '${ROOT_PASSWORD}';FLUSH PRIVILEGES;"

	# Redirect querry from website root to our main page
	rm /var/www/html/index.html
	echo "<?php header(\"Location: passoire/index.php\"); ?>" > /var/www/html/index.php

	# Link apache dir and our web dir
	ln -s /passoire/web/ /var/www/html/passoire

	service ssh restart
	service apache2 restart

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
