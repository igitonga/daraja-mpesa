
## About Daraja Mpesa

This is a web application to help you understand how to integrate daraja mpesa
in Laravel. There is a simple UI to run simulations and give a brief experience
on what might happen if you use it on your project.

To use this project on your personal PC follow the steps below:

### 1. Clone Repository
```bash
git clone https://github.com/KreateyouMain/bizcore.git
```
### 2. Install composer dependencies
```bash
composer install
```
### 3. Install node dependencies
```bash
npm install
```
### 4. Generate .env file from .env.example
```bash
cp .env.example .env
```
### 5. Migrate your database after connecting to one
```bash
php artisan migrate
```
### 5. Generate application key
```bash
php artisan key:generate
```
### 5. Run laravel server
```bash
php artisan serve
```
### 5. UI is bundled by vite to use interface.
```bash
npm run dev
```

- Fill Mpesa .env variables - you'll get these credentials from [Daraja website](https://developer.safaricom.co.ke/)


