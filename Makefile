init: docker-down docker-pull docker-build docker-up
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

composer-install:
	docker-compose run --rm php-cli composer install

# docker-compose run --rm php-cli composer install
# docker-compose run --rm php-cli php -v

