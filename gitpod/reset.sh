#!/bin/bash

docker-compose down
docker volume rm gitpod_config gitpod_db gitpod_nextcloud
docker-compose up --force-recreate --build