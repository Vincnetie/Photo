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

assets-install:
	docker-compose run --rm node yarn install

assets-rebuild:
	docker-compose run --rm npm rebuild node-sass --force

assets-dev:
	docker-compose run --rm node yarn run dev

assets-watch:
	docker-compose exec node yarn run watch

assets-build:
	docker-compose run --rm node npm run build

perm:
	sudo chown ${USER}:${USER} storage -R
	sudo chown ${USER}:${USER} package.json
	if [ -d "node_modules" ]; then sudo chown ${USER}:${USER} node_modules -R; fi
	if [ -d "public/build" ]; then sudo chown ${USER}:${USER} public/build -R; fi


# docker-compose run --rm node npm install --save @popperjs/core
# docker-compose run --rm php-cli composer require twbs/bootstrap:5.3.2 slim/twig-view
# sudo chmod 777 storage -R
# https://startbootstrap.com/themes?showAngular=false&showVue=false
