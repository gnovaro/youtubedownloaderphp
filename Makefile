.DEFAULT_GOAL := help

PHP       ?= php
COMPOSER  ?= composer
ARTISAN   := $(PHP) artisan
YTDLP     ?= /usr/local/bin/yt-dlp
ARCH      := $(shell uname -m)
YTDLP_ASSET := $(if $(filter aarch64 arm64,$(ARCH)),yt-dlp_linux_aarch64,yt-dlp_linux)
YTDLP_URL ?= https://github.com/yt-dlp/yt-dlp/releases/latest/download/$(YTDLP_ASSET)
DENO_URL  ?= https://deno.land/install.sh
WEB_GROUP ?= www-data
HOST      ?= 127.0.0.1
PORT      ?= 8000

# SQLite file configured in .env (DB_DATABASE). Empty when the default connection
# is not sqlite or the database is in memory. Evaluated only when used.
SQLITE_PATH = $(shell $(PHP) -r 'require "vendor/autoload.php"; $$app = require "bootstrap/app.php"; $$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); $$p = config("database.default") === "sqlite" ? (string) config("database.connections.sqlite.database") : ""; echo ($$p === ":memory:" || str_contains($$p, "mode=memory")) ? "" : $$p;' 2>/dev/null)

.PHONY: help install env key db migrate fresh permissions serve logs test lint lint-fix \
        clear optimize system-deps ytdlp-install ytdlp-update deno-install check

help: ## Show this help
	@grep -hE '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

## --- Application ---------------------------------------------------------

install: env ## Full setup: dependencies, .env, key, SQLite DB and migrations
	$(COMPOSER) install
	@$(PHP) -r 'require "vendor/autoload.php"; exit(empty(Dotenv\Dotenv::parse(file_get_contents(".env"))["APP_KEY"] ?? null) ? 1 : 0);' \
		|| $(ARTISAN) key:generate --ansi
	@$(MAKE) --no-print-directory db migrate

env: ## Create .env from .env.example if it does not exist
	@test -f .env || (cp .env.example .env && echo ".env created from .env.example")

key: ## Generate a new APP_KEY (replaces the current one)
	$(ARTISAN) key:generate --ansi

db: ## Create the SQLite database file configured in DB_DATABASE
	@path='$(SQLITE_PATH)'; \
	if [ -z "$$path" ]; then echo "No SQLite database file to create."; exit 0; fi; \
	mkdir -p "$$(dirname "$$path")"; \
	test -f "$$path" || (touch "$$path" && echo "Created $$path")

migrate: db ## Run database migrations
	$(ARTISAN) migrate --ansi

fresh: db ## Drop all tables and re-run migrations (destroys data)
	$(ARTISAN) migrate:fresh --ansi

permissions: ## Let the web server group (WEB_GROUP) write storage, cache and the SQLite DB (uses sudo)
	sudo chgrp -R $(WEB_GROUP) storage bootstrap/cache
	sudo chmod -R g+rwX storage bootstrap/cache
	@path='$(SQLITE_PATH)'; \
	if [ -n "$$path" ]; then \
		dir="$$(dirname "$$path")"; \
		sudo chgrp $(WEB_GROUP) "$$dir" "$$path" && sudo chmod g+rw "$$path" && sudo chmod g+rwx "$$dir"; \
	fi

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

system-deps: ## Install ffmpeg, curl, unzip, yt-dlp and Deno (uses sudo)
	sudo apt install -y ffmpeg curl unzip
	@$(MAKE) --no-print-directory ytdlp-install deno-install

ytdlp-install: ## Install the official yt-dlp binary in /usr/local/bin (uses sudo)
	@tmp=$$(mktemp) || exit 1; \
	trap 'rm -f "$$tmp"' EXIT; \
	curl -fL --retry 3 -o "$$tmp" "$(YTDLP_URL)" \
		&& chmod +x "$$tmp" \
		&& "$$tmp" --version >/dev/null \
		&& sudo install -m 0755 "$$tmp" "$(YTDLP)" \
		&& echo "yt-dlp $$($(YTDLP) --version) installed in $(YTDLP)"
	@if dpkg -s yt-dlp >/dev/null 2>&1; then \
		echo "Removing outdated apt yt-dlp package..."; \
		sudo apt remove -y yt-dlp; \
	fi

ytdlp-update: ## Update yt-dlp to the latest release (uses sudo)
	sudo $(YTDLP) -U

deno-install: ## Install Deno system-wide in /usr/local (uses sudo)
	@tmp=$$(mktemp) || exit 1; \
	trap 'rm -f "$$tmp"' EXIT; \
	curl -fsSL --retry 3 -o "$$tmp" "$(DENO_URL)" \
		&& sudo DENO_INSTALL=/usr/local sh "$$tmp"

check: ## Show versions of the required tools
	@$(PHP) -r 'echo "php     ", PHP_VERSION, PHP_EOL;'
	@printf "yt-dlp  "; command -v yt-dlp >/dev/null && yt-dlp --version || echo "NOT FOUND"
	@printf "deno    "; command -v deno >/dev/null && deno --version | head -1 || echo "NOT FOUND"
	@printf "ffmpeg  "; command -v ffmpeg >/dev/null && ffmpeg -version | head -1 || echo "NOT FOUND"
	@printf "sqlite  "; $(PHP) -r 'echo extension_loaded("pdo_sqlite") ? "pdo_sqlite OK" : "pdo_sqlite NOT LOADED (sudo apt install php$(shell $(PHP) -r "echo PHP_MAJOR_VERSION.\".\".PHP_MINOR_VERSION;")-sqlite3)", PHP_EOL;'
	@which yt-dlp 2>/dev/null | grep -q '^/usr/bin/' && \
		echo "WARNING: yt-dlp comes from apt (/usr/bin) and is probably outdated. Run: make ytdlp-install" || true
