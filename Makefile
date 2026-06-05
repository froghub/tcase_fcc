USER_ID = $(shell id -u)
GROUP_ID = $(shell id -g)

DOCKER_EXEC = docker compose exec --user $(USER_ID):$(GROUP_ID) php

.PHONY: yii composer up down test test2

yii:
	$(DOCKER_EXEC) php yii $(filter-out $@,$(MAKECMDGOALS))

composer:
	$(DOCKER_EXEC) composer $(filter-out $@,$(MAKECMDGOALS))

up:
	docker compose up -d
down:
	docker compose down
test:
	docker compose exec php php yii migrate/fresh --appconfig=config/console_test.php --interactive=0
	docker compose exec  php vendor/bin/codecept run Functional
%:
	@:
