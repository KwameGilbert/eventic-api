#!/bin/bash
# Seed the SQLite database on every container start
if [ -f /var/www/html/database/seed.sql ]; then
    sqlite3 /var/www/html/database/database.sqlite < /var/www/html/database/seed.sql
fi
exec apache2-foreground