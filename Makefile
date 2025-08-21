SHELL := /bin/bash

tests: export APP_ENV=test
tests:
	symfony console doctrine:database:drop --force || true
	symfony console doctrine:database:create
	symfony console doctrine:migrations:migrate -n
	symfony console doctrine:fixtures:load -n
	symfony php vendor/bin/phpunit --testdox $@
	php -d memory_limit=4G vendor/bin/phpstan
	vendor/bin/rector process --dry-run
.PHONY: tests
