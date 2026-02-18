Setup Instructions

Step 1: Clone the repository
Step 2: Run composer install
Step 3: Create the database
Step 4: Setup .env and setup database info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_test_2
DB_USERNAME=root
DB_PASSWORD=
APP_URL=http://localhost:8000
```

Step 5: Generate Application Key with php artisan key:generate

Step 6: php artisan migrate:fresh --seed

Step 7: run php artisan serve

**Admin Account:**
- Email: `admin@example.com` or `admin2@example.com`
- Password: `password`

