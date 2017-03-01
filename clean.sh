chmod 777 * -R
php composer.phar dump-autoload
php artisan clear-compiled
php artisan cache:clear
php artisan optimize
php artisan route:list
