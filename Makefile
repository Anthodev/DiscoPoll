isDocker := $(shell docker info > /dev/null 2>&1 && echo 1)
isContainerRunning := $(shell docker ps | grep discopoll > /dev/null 2>&1 && echo 1)
user := $(shell id -u)
group := $(shell id -g)

ifeq ($(isDocker), 1)
	ifeq ($(isContainerRunning), 1)
		DOCKER_COMPOSE := USER_ID=$(user) GROUP_ID=$(group) docker-compose
		DOCKER_EXEC := docker exec -u $(user):$(group) discopoll
		dr := $(DOCKER_COMPOSE) run --rm
		sf := $(DOCKER_EXEC) php bin/console
		drtest := $(DOCKER_COMPOSE) -f docker-compose.test.yml run --rm
		php := $(DOCKER_EXEC) php
	else
		DOCKER_COMPOSE := USER_ID=$(user) GROUP_ID=$(group) docker-compose
		DOCKER_EXEC :=
		sf := php bin/console
		php :=
	endif
else
	DOCKER_EXEC :=
	sf := php bin/console
	php :=
endif

COMPOSER = $(DOCKER_EXEC) composer
CONSOLE = $(DOCKER_COMPOSE) php bin/console

## —— App ————————————————————————————————————————————————————————————————
build-docker:
	$(DOCKER_COMPOSE) pull --ignore-pull-failures
	$(DOCKER_COMPOSE) build --no-cache

up:
	@echo "Launching containers from project $(COMPOSE_PROJECT_NAME)..."
	$(DOCKER_COMPOSE) up -d
	$(DOCKER_COMPOSE) ps

stop:
	@echo "Stopping containers from project $(COMPOSE_PROJECT_NAME)..."
	$(DOCKER_COMPOSE) stop
	$(DOCKER_COMPOSE) ps

destroy:
	@echo "Destroying containers from project $(COMPOSE_PROJECT_NAME)..."
	$(DOCKER_COMPOSE) down --remove-orphans
	$(DOCKER_COMPOSE) ps

## —— 🐝 The Symfony Makefile 🐝 ———————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?## .*$$)|(^## )' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
