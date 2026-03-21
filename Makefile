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
	@! grep -rPn '(?<![A-Za-z0-9_])\\[A-Z][A-Za-z0-9_]+\\[A-Z]' src/ --include="*.php" \
		| grep -vP '^[^:]+:\d+:\s*(use |namespace |//)' \
		| grep -vP '(@var|@param|@return|@throws|@extends|@implements|#\[)' \
		| grep -q . \
		|| (echo "ERROR: Fully-qualified class names found in src/ - use import statements instead" && exit 1)
.PHONY: tests
