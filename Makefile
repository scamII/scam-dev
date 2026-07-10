.PHONY: dev stop logs build sync-dev quality package deploy clean

SHELL := /usr/bin/env bash

dev: build sync-dev
	docker compose up -d
	@echo "WordPress: http://127.0.0.1:8080"
	@echo "phpMyAdmin: docker compose --profile debug up -d phpmyadmin"

stop:
	docker compose down

logs:
	docker compose logs -f

build:
	npm run build

sync-dev:
	bash scripts/sync-dev.sh

quality:
	npm run lint:js
	npm run lint:css:ci
	npm run test:static
	composer lint
	find . -type f -name '*.php' \
		-not -path './vendor/*' \
		-not -path './node_modules/*' \
		-print0 | xargs -0 -n1 php -l
	php tests/update-signing-test.php
	php tests/svg-sanitizer-test.php

package: build
	bash scripts/package.sh

deploy: package
	bash scripts/deploy.sh

clean:
	rm -rf build node_modules vendor .dev
	rm -f scam-dev-*.zip scam-dev-*.zip.sha256
