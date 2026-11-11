.DEFAULT_GOAL := help

PHP      ?= php
COMPOSER ?= composer
ARTISAN  := $(PHP) artisan
YTDLP    ?= /usr/local/bin/yt-dlp
DB_FILE  ?= database/ydownload.sqlite
HOST     ?= 127.0.0.1
PORT     ?= 8000

.PHONY: help install env key db migrate fresh serve logs test lint lint-fix \
        clear optimize system-deps ytdlp-install ytdlp-update deno-install check

help: ## Show this help
	@grep -hE '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

## --- Application ---------------------------------------------------------

install: env ## Full setup: dependencies, .env, key, SQLite DB and migrations
	$(COMPOSER) install
	@grep -q '^APP_KEY=base64:' .env || $(ARTISAN) key:generate --ansi
	@$(MAKE) --no-print-directory db migrate

env: ## Create .env from .env.example if it does not exist
	@test -f .env || (cp .env.example .env && echo ".env created from .env.example")

key: ## Generate a new APP_KEY
	$(ARTISAN) key:generate --ansi

db: ## Create the SQLite database file if it does not exist
	@test -f $(DB_FILE) || (touch $(DB_FILE) && echo "Created $(DB_FILE)")

migrate: db ## Run database migrations
	$(ARTISAN) migrate --ansi

fresh: db ## Drop all tables and re-run migrations (destroys data)
	$(ARTISAN) migrate:fresh --ansi

serve: ## Start the development server (HOST/PORT can be overridden)
	$(ARTISAN) serve --host=$(HOST) --port=$(PORT)

logs: ## Tail the application logs
	$(ARTISAN) pail --timeout=0

test: ## Run the test suite
	$(ARTISAN) test

lint: ## Check code style with Pint
	vendor/bin/pint --test

lint-fix: ## Fix code style with Pint
	vendor/bin/pint

clear: ## Clear all Laravel caches
	$(ARTISAN) optimize:clear

optimize: ## Cache config, routes and views (production)
	$(ARTISAN) optimize

## --- System dependencies (Ubuntu / Debian / WSL) ------------------------

system-deps: ## Install ffmpeg, yt-dlp and Deno (uses sudo)
	sudo apt install -y ffmpeg
	@$(MAKE) --no-print-directory ytdlp-install deno-install

ytdlp-install: ## Install the official yt-dlp binary in /usr/local/bin (uses sudo)
	@if dpkg -s yt-dlp >/dev/null 2>&1; then \
		echo "Removing outdated apt yt-dlp package..."; \
		sudo apt remove -y yt-dlp; \
	fi
	sudo curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp_linux -o $(YTDLP)
	sudo chmod a+rx $(YTDLP)

ytdlp-update: ## Update yt-dlp to the latest release (uses sudo)
	sudo $(YTDLP) -U

deno-install: ## Install Deno system-wide in /usr/local (uses sudo)
	curl -fsSL https://deno.land/install.sh | sudo DENO_INSTALL=/usr/local sh

check: ## Show versions of the required tools
	@$(PHP) -r 'echo "php     ", PHP_VERSION, PHP_EOL;'
	@printf "yt-dlp  "; command -v yt-dlp >/dev/null && yt-dlp --version || echo "NOT FOUND"
	@printf "deno    "; command -v deno >/dev/null && deno --version | head -1 || echo "NOT FOUND"
	@printf "ffmpeg  "; command -v ffmpeg >/dev/null && ffmpeg -version | head -1 || echo "NOT FOUND"
	@which yt-dlp 2>/dev/null | grep -q '^/usr/bin/' && \
		echo "WARNING: yt-dlp comes from apt (/usr/bin) and is probably outdated. Run: make ytdlp-install" || true
