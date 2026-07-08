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
	rm -rf node_modules .git .gitverse vendor plugins wp-content \
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
	HOST=$${DEPLOY_HOST:-$$(grep DEPLOY_HOST .env 2>/dev/null | cut -d= -f2)}; \
	USER=$${DEPLOY_USER:-$$(grep DEPLOY_USER .env 2>/dev/null | cut -d= -f2)}; \
	DOMAIN=$${SITE_DOMAIN:-$$(grep SITE_DOMAIN .env 2>/dev/null | cut -d= -f2)}; \
	if [ -z "$$HOST" ]; then echo "Set DEPLOY_HOST in .env file"; exit 1; fi; \
	echo "=== Deploying theme v$$VERSION ===" && \
	scp scam-dev-$$VERSION.zip $$USER@$$HOST:/var/www/html/scam-dev-$$VERSION.zip && \
	scp scam-dev-$$VERSION.zip $$USER@$$HOST:/var/www/html/scam-dev-latest.zip && \
	HASH=$$(sha256sum scam-dev-$$VERSION.zip | cut -d' ' -f1); \
	echo "{\"version\":\"$$VERSION\",\"download_url\":\"https://$$DOMAIN/scam-dev-$$VERSION.zip\",\"sha256\":\"$$HASH\",\"requires\":\"6.5\",\"requires_php\":\"8.0\"}" | ssh $$USER@$$HOST "cat > /var/www/html/theme-update.json" && \
	ssh $$USER@$$HOST "cd /var/www/html/wp-content/themes && rm -rf scam-dev && unzip -o /var/www/html/scam-dev-$$VERSION.zip && chown -R www-data:www-data scam-dev" && \
	echo "Theme deployed (sha256: $$HASH)" && \
	echo "=== Deploying plugins ===" && \
	for dir in plugins/*/; do \
		name=$$(basename $$dir); \
		echo "Packing $$name..."; \
		cd $$dir && zip -r ../../$$name.zip . > /dev/null && cd ../..; \
	done && \
	for zip in scam-dev-donate-widget.zip scam-dev-gallery.zip scam-dev-matrix.zip scam-dev-seo.zip scam-dev-svg.zip scam-dev-vk-import.zip; do \
		if [ -f "$$zip" ]; then \
			echo "Uploading $$zip..."; \
			scp $$zip $$USER@$$HOST:/var/www/html/wp-content/plugins/; \
		fi; \
	done && \
	ssh $$USER@$$HOST "cd /var/www/html/wp-content/plugins && for z in scam-dev-*.zip; do [ -f \"\$$z\" ] && unzip -o \"\$$z\" -d \"\$${z%.zip}\" && echo \"Extracted \$$z\"; done && rm -f scam-dev-*.zip" && \
	echo "Plugins deployed" && \
	echo "=== Done: v$$VERSION ==="

all: deploy

plugin:
	@rm -f scam-dev-*.zip
	@for dir in plugins/*/; do \
		name=$$(basename $$dir); \
		echo "Packing $$name..."; \
		cd $$dir && zip -r ../../$$name.zip . > /dev/null && cd ../..; \
	done
	@echo "Ready: $$(ls -1 scam-dev-*.zip | grep -v '[0-9]\.[0-9]' | tr '\n' ' ')"

clean:
	rm -rf build node_modules scam-dev-*.zip
