web: vendor/bin/heroku-php-apache2 public/
config: set APP_KEY=$(php artisan --no-ansi key:generate --show)
release: php artisan migrate:fresh --seed --force
