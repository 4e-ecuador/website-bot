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

