.PHONY: build
build:
	docker-compose -f docker/docker-compose.yml build --no-cache

.PHONY: start
start:
	docker-compose -f docker/docker-compose.yml up -d

.PHONY: stop
stop:
	docker-compose -f docker/docker-compose.yml stop