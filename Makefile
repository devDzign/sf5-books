SHELL := /bin/bash

tests:
	php bin/console doctrine:fixtures:load -n --env=test
	php bin/phpunit
.PHONY: tests