# Aksamedia Technical Test

A backend application for completing the Aksamedia technical test submission

## Requirements

1. Install PHP 8.1 - 8.2
2. Install Composer
3. Install MySQL

## How to Run

Clone the repository

    git clone https://github.com/dzakyy04/aksamedia-technical-test

Navigate to the repository folder

    cd aksamedia-technical-test

Install all dependencies using Composer

    composer install

Copy the .env.example file and set up the required configuration in the .env file

    cp .env.example .env

Generate a new application key

    php artisan key:generate

Run database migrations (Configure the database connection in .env before running migrations)

    php artisan migrate

Run the seeder to populate the database with initial data

    php artisan db:seed

Create a symbolic link

    php artisan storage:link

Generate API documentation

    php artisan l5-swagger:generate

Start the local development server

    php artisan serve

You can now access the server at [http://127.0.0.1:8000](http://127.0.0.1:8000) and the API documentation at [http://127.0.0.1:8000/api/documentation](http://127.0.0.1:8000/api/documentation)

