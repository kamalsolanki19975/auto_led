#!/bin/bash
# AutoAds DB bootstrap: idempotent provisioning on container start.
# Ensures the MariaDB app user + database exist, runs migrations, and seeds
# demo data only when the database is empty (e.g. after a snapshot restore).

for i in $(seq 1 60); do
  mysql -u root -e "SELECT 1" >/dev/null 2>&1 && break
  sleep 2
done

mysql -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS auto_ads CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'autoads'@'localhost' IDENTIFIED BY 'autoads_pass';
CREATE USER IF NOT EXISTS 'autoads'@'127.0.0.1' IDENTIFIED BY 'autoads_pass';
GRANT ALL PRIVILEGES ON auto_ads.* TO 'autoads'@'localhost';
GRANT ALL PRIVILEGES ON auto_ads.* TO 'autoads'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

chown -R www-data:www-data /app/laravel/storage /app/laravel/bootstrap/cache 2>/dev/null

cd /app/laravel
sudo -u www-data php artisan migrate --force

COUNT=$(mysql -u root -N -e "SELECT COUNT(*) FROM auto_ads.users" 2>/dev/null)
if [ "${COUNT:-0}" -eq 0 ]; then
  sudo -u www-data php artisan db:seed --force
fi

echo "db-bootstrap complete (users=${COUNT:-seeded})"
