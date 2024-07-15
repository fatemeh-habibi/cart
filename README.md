## Change bash file to run project
docker-compose up -d
docker-compose exec laravel php artisan migrate:fresh --seed
docker-compose exec laravel php artisan config:clear
docker-compose exec laravel php artisan passport:install
docker-compose exec laravel php artisan key:generate
docker-compose exec laravel php artisan config:cache
docker-compose exec laravel php artisan swagger:ge

Document:
http://localhost/api/documentation

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
