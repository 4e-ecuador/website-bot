## Setup

Setup Heroku env vars according to the `.env` file.

```yaml
# /config/routes.yaml

BoShurikTelegramBotBundle:
  resource: "@BoShurikTelegramBotBundle/Resources/config/routing.yml"
  prefix: '_telegram/%telegram_web_hook_secret%'
```

### Webpack

add nodejs

    heroku buildpacks:add heroku/nodejs

## Heroku

### Add DB
    heroku addons:create heroku-postgresql:hobby-dev
    heroku run php bin/console doctrine:migrations:migrate

### Reset DB

    heroku pg:reset DATABASE_URL

### Postgres import

    heroku pg:psql --app APP_NAME < dump.sql

### Backup
    heroku pg:backups:capture
    heroku pg:backups:download
    pg_restore latest.dump > latest.sql
    
### Migrations
     heroku run php bin/console doctrine:migrations:migrate --remote heroku-prod

### Restore docker database

* https://simkimsia.com/how-to-restore-database-dumps-for-postgres-in-docker-container/

1. **Find `name` and `ID`**<br>`docker ps`
2. **Find the volumes**<br>`docker inspect -f '{{ json .Mounts }}' <container_id> | python -m json.tool`
3. **Copy the dump**<br>`docker cp </path/to/dump/in/host> <container_name>:<path_to_volume>`
4. **Execute `psql`**<br> `docker exec <container_name> psql -U <database_owner> -d <database_name> -f <path_to_dump>`<br>NOTE: Use `docker exec -it ...` to use the psql binary from the docker container.

or better...
`cat backup.sql | docker exec -i 4e-website-bot_database_1 psql -U main`

## Google OAuth

https://hugo-soltys.com/blog/easily-implement-google-login-with-symfony-4

## Telegram bots

### Get updates
    https://api.telegram.org/bot<YourBOTToken>/getUpdates

## Update medal images and CSS

```text
bin/console app:update:badgedata
```

* Add the badge "code" in `src/Service/MedalChecker.php`
    See [This commit](https://github.com/4e-ecuador/website-bot/commit/ec7da179a0a4b469a0307938e96e271f9bb3eaec#diff-b27ba46e8094e3228d04607361f593fe)


### Emoji unicodes.

https://apps.timwhitlock.info/emoji/tables/unicode

### Security

* https://paragonie.com/blog/2017/02/split-tokens-token-based-authentication-protocols-without-side-channels
* https://stackoverflow.com/a/46207302/1906767
