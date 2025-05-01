#!/bin/sh

# create .env only when not exists
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env..."
    cat <<EOF > /var/www/.env
APP_NAME=${APP_NAME}
APP_ENV=${APP_ENV}
APP_DEBUG=false
APP_SECRET=$(openssl rand -hex 16)
APP_PLATFORM_EDITION=${APP_PLATFORM_EDITION}
DB_MASTER_PATH=${DB_MASTER_PATH}
DB_MASTER_DRIVER=${DB_MASTER_DRIVER}
EOF
fi


# Installation when db missing
if [ ! -f /var/www/var/garlic-hub.sqlite ]; then
    echo "Running install.php..."
    sqlite3 /var/www/var/garlic-hub.sqlite < migrations/edge/001_init.up.sql
	#php bin/console.php db:migrate 2>&1
else
    echo "Install already done."
fi

chown -R www-data:www-data /var/www/public/var /var/www/var
chmod -R 755 /var/www/public/var /var/www/var
umask 002


# Apache starten
exec apache2-foreground
