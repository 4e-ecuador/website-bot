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
`<path_to_volume>` should be `/var/lib/postgresql/data/dump.sql`<br>
`<container_name>` should be `websitebot_database_1`
1. `symfony server:start -d`
1. `symfony open:local`

## Credits

* Ingress Logos: http://cr0ybot.github.io/ingress-logos/
* Ingress badges: https://dedo1911.xyz/Badges

Devs: [dev.md](dev.md)
