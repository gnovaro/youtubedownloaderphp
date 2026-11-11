# YouTube Downloader PHP
Make your own YouTube Downloader Mp3 Site

## Requisites
* Linux
* php >= 8.4.1 with the `pdo_sqlite` extension (used by the default SQLite database)
* Laravel 12
* Web server
* Composer
* yt-dlp, latest release (https://github.com/yt-dlp/yt-dlp)
* Deno, the JavaScript runtime yt-dlp needs for YouTube (https://deno.com/)
* ffmpeg (https://ffmpeg.org/)


## Install ffmpeg, yt-dlp & Deno for video and audio conversions
### Linux (Ubuntu / Debian / WSL)
> **Don't install yt-dlp with `apt`.** The distribution package is usually outdated, and YouTube
> breaks old versions quickly (errors like `HTTP Error 400: Precondition check failed` or
> `Requested format is not available`).

Install ffmpeg, the SQLite PHP extension and the tools the installers below need:
```bash
sudo apt install -y ffmpeg curl unzip php8.4-sqlite3   # use the package for your PHP version
```

Install the official yt-dlp binary in `/usr/local/bin`, so every user, including the web server
user (`www-data`), can find it. It is downloaded to a temporary file first, so a failed download
does not break an existing installation:
```bash
case "$(uname -m)" in aarch64|arm64) asset=yt-dlp_linux_aarch64 ;; *) asset=yt-dlp_linux ;; esac
curl -fL -o /tmp/yt-dlp "https://github.com/yt-dlp/yt-dlp/releases/latest/download/$asset" \
  && sudo install -m 0755 /tmp/yt-dlp /usr/local/bin/yt-dlp \
  && rm /tmp/yt-dlp
```

If you previously installed yt-dlp with apt, remove it:
```bash
sudo apt remove -y yt-dlp
```

Install Deno system-wide:
```bash
curl -fsSL -o /tmp/deno-install.sh https://deno.land/install.sh \
  && sudo DENO_INSTALL=/usr/local sh /tmp/deno-install.sh \
  && rm /tmp/deno-install.sh
```

Check the installation:
```bash
yt-dlp --version
deno --version
ffmpeg -version
```

Keep yt-dlp up to date. When downloads stop working, updating it is usually the fix:
```bash
sudo yt-dlp -U
```

### Mac
```bash
brew install ffmpeg yt-dlp deno
```
Update with `brew upgrade yt-dlp`.

## Install Website
Copy the distributed environment file and set up the DB variables
```bash
cp .env.example .env
```
SQLite is used by default (requires the `pdo_sqlite` PHP extension). `DB_DATABASE` can be a
path relative to the project root (default `database/ydownload.sqlite`). Create the empty
database file at the path configured in `DB_DATABASE`, for the default:
```bash
touch database/ydownload.sqlite
```
Run composer, set the key and run the migrations
```bash
composer install
php artisan key:generate   # only on a fresh install: it replaces an existing APP_KEY
php artisan migrate
```

### Permissions for the web server
When the site is served by a web server (e.g. `www-data`) instead of `php artisan serve`, it needs
write access to `storage`, `bootstrap/cache`, the SQLite file **and its directory** (SQLite creates
journal files next to the database):
```bash
sudo chgrp -R www-data storage bootstrap/cache database
sudo chmod -R g+rwX storage bootstrap/cache
sudo chmod g+rwx database && sudo chmod g+rw database/ydownload.sqlite
```
Or run `make permissions` (set `WEB_GROUP=...` for a different group).

### Using the Makefile
The same steps are available as `make` targets (run `make` to list them all):
```bash
make system-deps   # ffmpeg, curl, unzip, yt-dlp and Deno (Ubuntu / Debian / WSL, uses sudo)
make install       # composer, .env, APP_KEY, SQLite DB and migrations
make check         # show versions of php, yt-dlp, deno, ffmpeg and pdo_sqlite
make ytdlp-update  # update yt-dlp when downloads stop working
make serve         # start the development server
make test          # run the tests
```

## Local test
```bash
php artisan serve
```
![Login](./docs/home_preview.png)

### Disable Auth in HomePage
![Login](./docs/login.png)
Just comment this line __construct of HomeController.php
```php
//$this->middleware('auth');
```

