## Setup

Setup Heroku env vars according to the `.env` file.

```yaml
# /config/routes.yaml

BoShurikTelegramBotBundle:
  resource: "@BoShurikTelegramBotBundle/Resources/config/routing.yml"
  prefix: '_telegram/%telegram_web_hook_secret%'
```

<del>

### Webpack

add nodejs

    heroku buildpacks:add --index 1 heroku/nodejs
</del>

## Heroku
 
### Reset DB

    heroku pg:reset DATABASE_URL

### Postgres import

    heroku pg:psql --app APP_NAME < dump.sql

