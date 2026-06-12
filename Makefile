.PHONY: dev prod build clean install

dev:
	docker compose up -d
	@echo "  WordPress: http://localhost:8080  |  PhpMyAdmin: http://localhost:8081"

stop:
	docker compose down

logs:
	docker compose logs -f

build:
	npm run build
	@mkdir -p assets/css assets/js
	@cp build/*.css assets/css/ 2>/dev/null || true
	@cp build/app.js assets/js/ 2>/dev/null || true

prod: build
	@rm -f scam-dev-*.zip; \
	VERSION=$$(grep -m1 '^Version:' style.css | cut -d: -f2 | tr -d ' '); \
	NEW_VERSION=$$(echo $$VERSION | awk -F. '{print $$1"."$$2"."$$3+1}'); \
	sed -i "s|^Version: .*|Version: $$NEW_VERSION|" style.css; \
	mkdir -p /tmp/scam-dev-dist/scam-dev && \
	cp -r . /tmp/scam-dev-dist/scam-dev/ && \
	cd /tmp/scam-dev-dist/scam-dev && \
	rm -rf node_modules .git vendor plugins wp-content \
		Makefile docker-compose.yml .dockerignore \
		composer.* postcss.config.js webpack.config.js \
		package.json package-lock.json \
		.gitignore .eslintrc.json .config.json .env \
		inc/search-bar.php \
		start.sh PROJECT.md project-info.sh \
		assets/js/main.js assets/js/app.js \
		assets/js/components assets/scss *.zip && \
	mkdir -p assets/css assets/js && \
	mv build/*.css assets/css/ && \
	mv build/app.js assets/js/ && \
	rm -rf build && \
	cd /tmp/scam-dev-dist/scam-dev && \
	find . -type d -exec chmod 755 {} \; && \
	find . -type f -exec chmod 644 {} \; && \
	cd /tmp/scam-dev-dist && \
	zip -r $(CURDIR)/scam-dev-$$NEW_VERSION.zip scam-dev/ > /dev/null && \
	rm -rf /tmp/scam-dev-dist && \
	echo "Ready: scam-dev-$$NEW_VERSION.zip ($$(du -h $(CURDIR)/scam-dev-$$NEW_VERSION.zip | cut -f1))"

deploy: prod
	@VERSION=$$(grep -m1 '^Version:' style.css | cut -d: -f2 | tr -d ' '); \
	scp scam-dev-$$VERSION.zip root@YOUR_SERVER_IP:/var/www/html/scam-dev-$$VERSION.zip && \
	scp scam-dev-$$VERSION.zip root@YOUR_SERVER_IP:/var/www/html/scam-dev-latest.zip && \
	echo "{\"version\":\"$$VERSION\",\"download_url\":\"https://your-domain.ru/scam-dev-$$VERSION.zip\",\"requires\":\"6.5\",\"requires_php\":\"8.0\"}" | ssh root@YOUR_SERVER_IP "cat > /var/www/html/theme-update.json" && \
	echo "Deployed v$$VERSION to your-domain.ru"

all: deploy plugin

clean:
	rm -rf build node_modules scam-dev-*.zip scam-dev-gallery.zip
