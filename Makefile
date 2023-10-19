init: docker-down docker-pull docker-build docker-up api-init
up:	docker-up
down: docker-down
restart: down up
clear: docker-down-clear docker-pull docker-build docker-up
del: docker-down-clear

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build --pull

api-init: api-composer-install api-composer-dump-autoload

api-composer-install:
	docker-compose run --rm php-cli composer install

api-composer-dump-autoload:
	docker-compose run --rm php-cli composer dump-autoload

# docker-compose run --rm php-cli composer require slim/twig-view
# sudo chown $USER:$USER storage -R
# sudo chmod 777 storage -R
# https://startbootstrap.com/themes?showAngular=false&showVue=false