[![Build Status](https://travis-ci.org/4e-ecuador/website-bot.svg?branch=master)](https://travis-ci.org/4e-ecuador/website-bot)

# This is:

* A Symfony web site
* A Telegram bot

## Setup

1. `git clone` this repository
1. `cd` to repo
1. `composer install`
1. `npm install`
1. `npm run dev`
1. `docker-compose up -d`
1. `docker cp </path/to/dump/in/host> <container_name>:<path_to_volume>`<br>
e.g.: `docker cp backups/dump.sql website-bot_database_1:/dump.sql`
1. `docker exec -it <container_name> psql -U <database_owner> -d <database_name> -f <path_to_dump>`<br>
e.g.: `docker exec -it website-bot_database_1 psql -U main -d main -f /dump.sql`
1. `symfony server:start -d`
1. `symfony open:local`

NOTE: If you deploy a NEW setp, omit the steps `7` and `8` and just run `doctrine:database:create` and `doctrine:fixtures:load`

## Credits

* Ingress Logos: http://cr0ybot.github.io/ingress-logos/
* Ingress badges: https://dedo1911.xyz/Badges

Devs: [dev.md](dev.md)
