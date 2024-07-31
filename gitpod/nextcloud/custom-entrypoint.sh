#!/bin/bash

# Both gitpod and Nextcloud need read/write acess
# Obviously not recommended for a production system
chown -R www-data:www-data /var/www/html/custom_apps/
chmod -R 777 /var/www/html/custom_apps/

/entrypoint.sh "$@"