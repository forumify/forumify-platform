PHP_VER:=8.3
SHELL:=/bin/bash
ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

.PHONY: quality
quality:
	@docker run --rm -v "$(ROOT_DIR):/project" -w /project jakzal/phpqa:php$(PHP_VER)-alpine phpcs -s --standard=phpcs.xml
	@docker run --rm -v "$(ROOT_DIR):/project" -w /project jakzal/phpqa:php$(PHP_VER)-alpine phpstan analyze -v

.PHONY: quality-fix
quality-fix:
	@docker run --rm -v "$(ROOT_DIR):/project" -w /project jakzal/phpqa:php$(PHP_VER)-alpine phpcbf --standard=phpcs.xml
