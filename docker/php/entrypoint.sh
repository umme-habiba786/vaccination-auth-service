if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing composer."
    composer install --no-progress --no-interaction
    echo "Composer installed."
fi

if [ ! -f ".env" ]; then
    echo "Creating env file for env $APP_ENV"
    cp .env.example .env
fi

php artisan jwt:secret -f
php artisan cache:clear

exec "$@"
