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

    heroku buildpacks:add heroku/nodejs
</del>

## Heroku
 
### Reset DB

    heroku pg:reset DATABASE_URL

### Postgres import

    heroku pg:psql --app APP_NAME < dump.sql

## Google OAuth

https://hugo-soltys.com/blog/easily-implement-google-login-with-symfony-4
