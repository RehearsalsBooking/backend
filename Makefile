#!/usr/bin/make
# Makefile readme (ru): <http://linux.yaroslavl.ru/docs/prog/gnu_make_3-79_russian_manual.html>
# Makefile readme (en): <https://www.gnu.org/software/make/manual/html_node/index.html#SEC_Contents>

SHELL = /bin/sh

php_container_name := php
docker_bin := $(shell command -v docker 2> /dev/null)
docker_compose_bin := $(shell command -v docker-compose 2> /dev/null)
docker_compose_yml := docker/docker-compose.yml
user_id := $(shell id -u)

.PHONY : help pull build push login test clean \
         app-pull app app-push\
         sources-pull sources sources-push\
         nginx-pull nginx nginx-push\
         up down restart shell install
.DEFAULT_GOAL := help

# --- [ Development tasks ] -------------------------------------------------------------------------------------------
help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

build: check-environment ## Build container and install dependencies
	$(docker_compose_bin) --file "$(docker_compose_yml)" build
	$(docker_compose_bin) --file "$(docker_compose_yml)" run --rm -u $(user_id) "$(php_container_name)" composer install

update: check-environment ## Build update dependencies
	$(docker_compose_bin) --file "$(docker_compose_yml)" run --rm -u $(user_id) "$(php_container_name)" composer update

test: check-environment ## Execute tests
	$(docker_compose_bin) --file "$(docker_compose_yml)" run --rm -u $(user_id) "$(php_container_name)" vendor/bin/phpunit

shell: check-environment ## Run shell environment in container
	$(docker_compose_bin) --file "$(docker_compose_yml)" run --rm -u $(user_id) "$(php_container_name)" /bin/bash

docs: check-environment ## Generate docs for models
	$(docker_compose_bin) --file "$(docker_compose_yml)" run --rm -u $(user_id) "$(php_container_name)" /bin/bash -c "php artisan migrate:fresh"
	$(docker_compose_bin) --file "$(docker_compose_yml)" run --rm -u $(user_id) "$(php_container_name)" /bin/bash -c "php /app/artisan ide-helper:models -W"

tinker: check-environment ## Run tinker inside container
	$(docker_compose_bin) --file "$(docker_compose_yml)" run --rm -u $(user_id) "$(php_container_name)" /bin/bash -c "php artisan tinker"

seed: check-environment ## Seeds db with dummy data
	$(docker_compose_bin) --file "$(docker_compose_yml)" run --rm -u $(user_id) "$(php_container_name)" /bin/bash -c "php artisan migrate:fresh --seed"

stop-all: ## Stop all containers
	$(docker_compose_bin) --file "$(docker_compose_yml)" down

# Check whether the environment file exists
check-environment:
ifeq ("$(wildcard .env)","")
	- @echo Copying ".env.example";
	- cp .env.example .env
endif

# Prompt to continue
prompt-continue:
	@while [ -z "$$CONTINUE" ]; do \
		read -r -p "Would you like to continue? [y]" CONTINUE; \
	done ; \
	if [ ! $$CONTINUE == "y" ]; then \
        echo "Exiting." ; \
        exit 1 ; \
    fi
