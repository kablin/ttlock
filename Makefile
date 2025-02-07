
####### VARS #######

include .env
#export $(shell sed 's/=.*//' .env)

#ifeq ($(APP_ENV), production)
# TYPE_ENV := prod
#else
# TYPE_ENV := dev
#endif

####### DOCKER #######



up: 
	docker compose -f docker-compose.dev.yml  up -d

 

down:

	docker compose -f docker-compose.dev.yml stop ${service}


null:
	docker compose -f docker-compose.dev.yml build --no-cache

docker-stop:
	docker compose  stop ${service}

docker-down:
	docker compose  down


docker-build:
	docker compose  -f docker-compose.dev.yml up --build -d ${service}

build: docker-build

docker-build-no-deps:
	docker-compose  up --build -d --no-deps ${service}

docker-log:
	docker compose  logs ${service}


queue-restart:
	docker compose  exec -u root -T php-fpm supervisorctl restart queue

sql:
	docker exec -it ttlockdb bash



phpfpm:
	docker compose  -f docker-compose.dev.yml exec spt2fpm bash  


dev:
	docker compose  exec -it spt2-cli  php artisan serve --host 0.0.0.0 --port 8000



php:
	docker compose  -f docker-compose.dev.yml exec ttlockcli bash  

node:     
	docker compose  -f docker-compose.dev.yml exec ttlocknode bash





nginx:   
	docker compose -f docker-compose.dev.yml up   -d --no-deps --build spt2nginx

nginxstart:  
	docker exec sptspttimeline-nginx nginx -s reload

nginxe:  
	docker cp /home/kablin/spttimeline/docker/default.conf sptspttimeline-nginx:/etc/nginx/conf.d/default.conf       
	docker compose -f docker-compose.dev.yml  exec -T spt2nginx sh -c "nginx -s reload"

	