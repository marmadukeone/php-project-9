PORT ?= 8000

start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public

install:
	composer install

validate:
	composer validate

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src app public tests
	composer exec --verbose phpstan -- --level=8 analyse src app public tests

lint-fix:
	composer exec --verbose phpcbf -- --standard=PSR12 public src app

test:
	composer exec --verbose phpunit tests