#!/bin/bash
set -e

# Fix permissions for storage and cache
# We use chmod 777 because volume mounts can mess up ownership, and this is the most robust way for "easy deployment"
# especially for Sandbox mode where users might just run docker-compose up.
if [ -d "/var/www/html/storage" ]; then
    chmod -R 777 /var/www/html/storage
fi

if [ -d "/var/www/html/bootstrap/cache" ]; then
    chmod -R 777 /var/www/html/bootstrap/cache
fi

# Execute the passed command (e.g., apache2-foreground)
exec "$@"
