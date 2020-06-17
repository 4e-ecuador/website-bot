web: vendor/bin/heroku-php-apache2 public/
release: ./bin/console doctrine:migrations:migrate --allow-no-migration && ./bin/console HerokuDeployFinishedNofitication
