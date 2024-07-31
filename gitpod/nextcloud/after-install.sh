#!/bin/bash

run_as() {
    if [ "$(id -u)" = 0 ]; then
        su -p www-data -s /bin/sh -c "$1"
    else
        sh -c "$1"
    fi
}

is_installed() {
    echo "$(run_as 'php /var/www/html/occ status --output=json' | jq '.installed')"
}

wait_until_finished() {
    until [[ $(is_installed) == "true" ]]; do
        echo "waiting for nextcloud to initialize, so groupfolder_tags can be activated"
        run_as "php /var/www/html/occ status --output=json"
        sleep 5
    done
}

echo "$(is_installed)"

wait_until_finished

run_as "php /var/www/html/occ config:system:set debug --value='true' --type=boolean"

run_as "php /var/www/html/occ app:enable groupfolder_tags"

run_as "php /var/www/html/occ migrations:migrate groupfolder_tags"

run_as "php /var/www/html/occ app:disable firstrunwizard"

run_as "php /var/www/html/occ config:system:set defaultapp --value='groupfolder_tags'"

apache2-foreground