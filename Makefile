.PHONY: quality
quality:
	@./vendor/bin/phpcs
	@./vendor/bin/phpstan

.PHONY: tests
tests:
	make setup-tests
	make run-tests

.PHONY: setup-tests
setup-tests:
	@cd tests && php bin/console doctrine:database:drop --force --env=test
	@cd tests && php bin/console doctrine:database:create --env=test
	@cd tests && php bin/console doctrine:migrations:migrate --no-interaction --env=test
	@cd tests && php bin/console forumify:platform:setting -k forumify.platform_installed --value true
	@cd tests && php bin/console forumify:plugins:refresh --env=test

.PHONY: run-tests
run-tests:
	@./vendor/bin/phpunit
