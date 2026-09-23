# ISSEY PARFUMS

Source repository: [yesi_parfums](https://github.com/blackhatnovus-cyber/yesi_parfums).

ISSEY PARFUMS is a full-stack perfume store built with Laravel, MySQL, Blade, vanilla JavaScript, and Vite. It includes a customer storefront and a protected administration dashboard for products, categories, orders, customers, and contact messages.

The project was created by Issey A. Cabangon for Section 3-B, Event Driven Programming, 2026.

For a guided tour of the source code, see [CODE_EXPLANATION.md](CODE_EXPLANATION.md).

## Features

- Responsive perfume catalogue, search, sorting, and product details
- Username/password authentication with login throttling
- Persistent cart and wishlist for authenticated customers
- Transactional checkout with inventory validation
- Customer order details and item snapshots
- Protected, role-based admin dashboard
- Product CRUD, image uploads, status changes, and soft deletion
- Category, order, customer, and contact-message management
- Responsive storefront and admin interfaces
- Automated feature and security tests

## Requirements

- PHP 8.3 or later
- Composer 2
- MySQL 8 or MariaDB 10.6+
- Node.js 20.19+ or 22.12+
- npm
- Git (to clone this repository)
- PHP extensions normally required by Laravel, including PDO MySQL, Mbstring, OpenSSL, Tokenizer, XML, Ctype, Fileinfo, and BCMath

Laragon is recommended on Windows because it includes PHP, MySQL, and a local web server.

## Fresh installation

### 1. Download the project

In PowerShell:

```powershell
cd C:\laragon\www
git clone https://github.com/blackhatnovus-cyber/yesi_parfums.git
cd yesi_parfums
```

On macOS or Linux, open a terminal in your preferred projects directory, then run the same `git clone` and `cd yesi_parfums` commands. If you already downloaded the project, open its directory and skip cloning.

### 2. Install PHP and JavaScript dependencies

```powershell
composer install
npm ci
```

### 3. Create the environment file

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

On macOS or Linux, use `cp .env.example .env` instead of `Copy-Item`.

### 4. Create the MySQL database

Start MySQL in Laragon, open HeidiSQL or another MySQL client, and run:

```sql
CREATE DATABASE issey_parfums
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

The default `.env.example` settings are:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=issey_parfums
DB_USERNAME=root
DB_PASSWORD=
```

Update these values in `.env` when your MySQL username, password, port, or database name is different.

### 5. Create the tables and demonstration data

```powershell
php artisan migrate --seed
```

This creates the storefront and admin tables, ten sample perfume products, the demonstration customer, and the administrator account.

Do not skip `--seed` on a fresh installation. Running only `php artisan migrate` creates empty tables; the shop will show `0 fragrances`. Your previous computer's database is not included in a GitHub download.

### 6. Make uploaded images public

```powershell
php artisan storage:link
```

This creates `public/storage`, allowing images uploaded through the admin dashboard to be displayed by the website.

### 7. Build the frontend assets

```powershell
npm run build
```

### 8. Start the application

```powershell
php artisan serve
```

Open <http://127.0.0.1:8000>. The protected dashboard is at <http://127.0.0.1:8000/admin>.

Keep this terminal running while using the application. Press `Ctrl+C` to stop it. On later visits, start MySQL and run `php artisan serve` from the project folder; you do not need to repeat installation or seeding.

For Laragon virtual hosts, configure the document root to this project's `public` directory. The repository folder is named `yesi_parfums`, while the application's existing brand and default database name remain `Issey Parfums` and `issey_parfums`.

## Development mode

Use two terminals while changing CSS or JavaScript.

Terminal 1:

```powershell
php artisan serve
```

Terminal 2:

```powershell
npm run dev
```

Vite will rebuild the frontend immediately when a source file changes.

## Demonstration accounts

### Administrator

- Username: `admin`
- Email: `admin@isseyparfums.test`
- Password: `admin123`
- Destination after login: `/admin`

### Customer

- Username: `issey`
- Email: `issey@example.com`
- Password: `password`
- Destination after login: `/shop`

These credentials are intended for local demonstration only. Change the administrator password before exposing the application to the internet.

## Updating an existing installation safely

Do not run `php artisan migrate:fresh` on an existing installation. That command drops every table and deletes the current data.

Back up the database first, then run:

```powershell
composer install
npm ci
php artisan migrate
npm run build
php artisan optimize:clear
```

Run `php artisan storage:link` if the storage link does not exist yet. Do not rerun the demonstration seeders during routine updates: they reset demonstration account passwords and can overwrite sample product data.

Local databases, uploaded files, backups, `.env`, `vendor`, and `node_modules` are excluded from this repository. A fresh installation uses migrations and seeders to create its own database. Back up your own database and uploaded files before applying updates.

## Useful commands

```powershell
# Show every migration and its status
php artisan migrate:status

# List application routes
php artisan route:list

# Run the complete automated test suite
php artisan test

# Check PHP formatting
vendor\bin\pint --test

# Build production assets
npm run build

# Clear cached Laravel configuration and views
php artisan optimize:clear
```

## Tests

The tests use an isolated SQLite in-memory database configured by `phpunit.xml`; running them does not modify the MySQL development database.

```powershell
php artisan test
```

The suite covers authentication, role protection, catalogue filtering, cart and wishlist ownership, checkout and stock changes, admin CRUD operations, status updates, uploads, validation, and contact messages.

## Troubleshooting

### Shop shows `0 fragrances` after installation

For a new local installation, open a terminal in the project folder and run:

```powershell
php artisan config:clear
php artisan migrate --seed
```

Refresh the Shop page and clear any search text. The seeder creates ten sample perfumes with images included in `public/images`.

If products are still missing, confirm the database settings in `.env` match the database you created in step 4. In Admin > Products, confirm products are active and have not been deleted. The storefront only displays active, non-deleted products.

Seeding resets the `admin` and `issey` demonstration passwords and overwrites matching sample product details. Use it for fresh demonstration installations, not routine updates to a store with existing data. Do not use `migrate:fresh` to fix this issue because it deletes all tables.

### PowerShell says `npm.ps1 cannot be loaded`

Use `npm.cmd ci`, `npm.cmd run build`, or `npm.cmd run dev` in place of the corresponding `npm` command. This works without changing PowerShell's execution policy. Alternatively, use Command Prompt or Laragon's terminal.

### `Unknown database 'issey_parfums'`

Create the database described in step 4 and verify `DB_DATABASE` in `.env`.

### `Access denied for user`

Correct `DB_USERNAME` and `DB_PASSWORD` in `.env`, then run:

```powershell
php artisan config:clear
```

### `Vite manifest not found`

Install and build the frontend dependencies:

```powershell
npm install
npm run build
```

### Uploaded product image returns 404

Create the public storage link:

```powershell
php artisan storage:link
```

### Port 8000 is already in use

Start Laravel on another port:

```powershell
php artisan serve --port=8010
```

Then open <http://127.0.0.1:8010>.

### Login displays `Page Expired` or HTTP 419

Clear cached configuration, refresh the page, and submit the form again:

```powershell
php artisan optimize:clear
```

Also confirm that the `sessions` table exists by running `php artisan migrate:status`.

### PHP says OpenSSL is already loaded

This is a PHP configuration warning, not an application error. Open the active `php.ini` and ensure the OpenSSL extension is enabled only once.

## Main project directories

| Path | Purpose |
|---|---|
| `app/Http/Controllers` | Storefront, authentication, cart, wishlist, and checkout request handling |
| `app/Http/Controllers/Admin` | Protected admin dashboard and management actions |
| `app/Http/Requests` | Validation and authorization rules |
| `app/Models` | Eloquent models, relationships, casts, and status constants |
| `database/migrations` | Additive database schema history |
| `database/seeders` | Demonstration users, categories, and products |
| `resources/views` | Blade templates for storefront and admin pages |
| `resources/css` | Separate storefront and admin stylesheets |
| `resources/js` | Event handlers and Fetch-based UI updates |
| `routes/web.php` | Public, authenticated-customer, and admin routes |
| `tests` | Automated unit and feature tests |

## License and academic use

This repository is an academic project. Laravel itself is open-sourced software licensed under the MIT license.
