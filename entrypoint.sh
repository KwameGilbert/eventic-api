#!/bin/bash
# Seed the MySQL database on every container start
if [ -f /var/www/html/database/seed.sql ]; then
    mysql -h "$PROD_DB_HOST" -P "$PROD_DB_PORT" -u "$PROD_DB_USERNAME" -p"$PROD_DB_PASSWORD" "$PROD_DB_DATABASE" < /var/www/html/database/seed.sql
fi
exec apache2-foreground