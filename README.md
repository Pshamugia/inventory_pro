# Inventory Pro

Inventory Pro is a Laravel-based inventory and POS application.

## Local Apache setup

Laravel must be served from the `public` directory. If Apache/XAMPP is pointed at the project root, requests such as `/login` can return Apache's plain `Not Found` page instead of reaching Laravel.

Recommended virtual host/document root:

```apache
DocumentRoot "C:/path/to/inventory_pro/public"
<Directory "C:/path/to/inventory_pro/public">
    AllowOverride All
    Require all granted
</Directory>
```

Also make sure Apache's `mod_rewrite` module is enabled. A root `.htaccess` file is included as a local fallback for project-root document roots, but pointing Apache directly to `public` is still the preferred setup.

## Development

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

Run the test suite with:

```bash
composer test
```
