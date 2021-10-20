# Studi Kasus Point of Sales

## Compiles and Running

- docker-compose build
- docker-compose up -d
- setting database connection in `.env`
- docker-compose exec php php /var/www/html/artisan migrate
- docker-compose exec php php /var/www/html/artisan db:seed
- docker-compose exec php php /var/www/html/vendor/bin/phpunit
- visit http://localhost:8080

## Test with Postman
- Silahkan cek di modul atau materi **MENGUJI DENGAN POSTMAN** 

> Source code ini merupakan bagian dari Materi Ebook [Membangun Restful API dengan Lumen](https://santrikoding.com/ebook/membangun-restful-api-aplikasi-kasir-dengan-lumen)
