[![Build Status](https://travis-ci.org/4e-ecuador/website-bot.svg?branch=master)](https://travis-ci.org/4e-ecuador/website-bot)

# This is:

* A Symfony web site
* A Telegram bot

## Setup

1. `git clone` this repository
1. `cd` to repo
1. `composer install`
1. `yarn`
1. `yarn dev`
1. `docker-compose up -d` - or setup a PostgreSQL database "by hand" ;)
1. `symfony console doctrine:schem:create` - only for a NEW setup! (see below ⬇ )
1. `symfony console doctrine:fixtures:load` - only for a NEW setup! (see below ⬇ )
1. `symfony server:start -d`
1. `symfony open:local`

NOTE: If you deploy the site with a database dump, omit the steps `7` and `8` and import the database to docker or other database:

* 7 `docker cp </path/to/dump/in/host> <container_name>:<path_to_volume>`<br>
e.g.: `docker cp backups/dump.sql website-bot_database_1:/dump.sql`
* 8 `docker exec -it <container_name> psql -U <database_owner> -d <database_name> -f <path_to_dump>`<br>
e.g.: `docker exec -it website-bot_database_1 psql -U main -d main -f /dump.sql`

## Credits

* Ingress Logos: http://cr0ybot.github.io/ingress-logos/
* Ingress badges: https://dedo1911.xyz/Badges

Devs: [dev.md](dev.md)

**Disclaimer** Of course this is not affiliated or related in any way with [Ingress](https://ingress.com), [Niantic](https://nianticlabs.com), [Google](https://google.com) or the [Bundesnachrichtendienst](https://www.bnd.bund.de) ;=) 
