
## About: VSP (Virtual Soccer Project)

VSP is a soccer online manager game API, where football/soccer fans will create fantasy teams and will be able to sell or buy players.

Dependencies:

- Php ^7.3
- Laravel ^8.12
- Composer ^2.0
- Sanctum ^2.9
- Faker ^1.9
  
#Installation

- Clone Repo Locally.
- Run `composer install` from your terminal in project dir.
- Configure database connection on `.env`.
- Run `php artisan migrate` to migrate tables.
- Run `php artisan test` to make sure all test case passes (optional).
- Run `php artisan db:seed` to seed tables (optional).
- If you skipped the above:
  - Run `php artisan db:seed --class=UsersTableSeeder` to seed admin
  - Email: _admin@admin.com_
  - Password: _1234567890_
- Run `php artisan route:list` to see available routes.
- Deploy and start making request, see the postman collection for samples.
