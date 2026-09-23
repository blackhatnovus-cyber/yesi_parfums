# ISSEY PARFUMS Code Explanation

This document explains how the application is organized, how a browser request moves through the code, and where to make common changes. It is a practical guide for a project presentation, code review, or future maintenance.

## 1. Technology overview

| Layer | Technology | Responsibility |
|---|---|---|
| Backend | Laravel 13 and PHP 8.3 | Routing, validation, authentication, business rules, and database access |
| Database | MySQL | Users, products, carts, wishlists, orders, categories, messages, sessions, and cache |
| Templates | Blade | Server-rendered storefront and admin HTML |
| Browser behavior | Vanilla JavaScript | Menus, search, cart/wishlist updates, status controls, and toast messages |
| Styling | CSS with Vite | Separate responsive storefront and admin designs |
| Tests | PHPUnit through Laravel | HTTP behavior, authorization, validation, database changes, and security boundaries |

The application follows Laravel's Model-View-Controller pattern:

1. A browser sends a request to a URL.
2. `routes/web.php` connects that URL to a controller method.
3. Middleware checks whether the request is allowed.
4. A Form Request validates submitted values.
5. The controller applies the business rules and uses Eloquent models.
6. Models read or write records in MySQL.
7. The controller returns a Blade view, redirect, or JSON response.
8. JavaScript may update part of the page without a full reload.

## 2. Route groups and access control

All browser routes are defined in `routes/web.php`.

### Public routes

The home page, shop, search, product details, portfolio, contact page, login page, and simulated password-recovery page are available without authentication.

Important controllers:

- `HomeController` loads featured and portfolio products.
- `ProductController` lists, searches, sorts, and displays active products.
- `ContactController` stores messages submitted through the contact form.
- `AuthController` logs users in and out and handles the recovery simulation.

### Authenticated customer routes

The `auth` middleware protects cart, wishlist, checkout, order details, and logout. A guest who opens one of these pages is redirected to `/login`.

Important controllers:

- `CartController` adds, updates, removes, and totals cart items.
- `WishlistController` toggles and removes saved products.
- `CheckoutController` validates shipping details, creates an order, changes stock, and displays the customer's order.

### Administrator routes

Every route beginning with `/admin` uses both `auth` and `admin` middleware:

```php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(...);
```

Laravel's authentication middleware first confirms that someone is logged in. `app/Http/Middleware/EnsureUserIsAdmin.php` then confirms that the user has the `admin` role and an active account. A customer cannot access an admin page simply by typing its URL.

## 3. Authentication flow

The login form sends a `POST` request to `/login`.

1. `LoginRequest` trims the username and validates the username and password fields.
2. `AuthController@login` builds a rate-limit key from the lowercase username and IP address.
3. Laravel attempts authentication using the username, password, and `active` status.
4. Failed attempts are rate-limited and return a safe generic error.
5. Successful login clears the throttle and regenerates the session identifier.
6. Administrators are sent to `/admin`; customers are sent to `/shop`.
7. Logout invalidates the complete session and creates a new CSRF token.

Passwords are never stored as plain text. The `User` model casts passwords as `hashed`, and the seeders use Laravel's `Hash` facade.

## 4. Main database models

| Model | Important relationships and purpose |
|---|---|
| `User` | Has one cart, has one wishlist, and has many orders; stores customer/admin role and account status |
| `Product` | Belongs to a category and has cart, wishlist, and order items; supports active/inactive state and soft deletion |
| `Category` | Has many products |
| `Cart` | Belongs to a user and has many cart items |
| `CartItem` | Belongs to a cart and a product; stores quantity |
| `Wishlist` | Belongs to a user and has many wishlist items |
| `WishlistItem` | Belongs to a wishlist and a product |
| `Order` | Belongs to a user and has many order items; stores shipping snapshots, total, and status |
| `OrderItem` | Belongs to an order and optionally a product; stores product-name and price snapshots |
| `ContactMessage` | Stores the public contact submission and its unread/read/replied status |

### Why order items contain snapshots

An order item stores `product_name`, `product_image`, `unit_price`, `quantity`, and `subtotal`. This preserves the exact purchase history even if an administrator later renames, reprices, changes the image of, or deletes the original product.

### Why products use soft deletion

Deleting a product sets `deleted_at` instead of physically removing the database row. This protects historical references and prevents an old order from losing its product relationship. Public queries use the model's active scope, so inactive and soft-deleted products do not appear in the storefront.

## 5. Customer storefront flow

### Product catalogue and search

`ProductController` starts an Eloquent query using `Product::active()`. Search checks the product name, category, and description. Sorting is selected from an allow-list before any SQL ordering is applied.

The browser sends search requests to `/products/search`. The controller returns JSON containing rendered Blade partials. `resources/js/app.js` replaces the product results without reloading the whole page.

### Cart

Adding or changing a cart item is handled inside a database transaction. The controller locks the product row, checks that the product is active, verifies available stock, and then writes the quantity. It also confirms that a user owns an item before allowing it to be changed or removed.

The JSON response contains updated HTML, cart count, totals, and a message. JavaScript replaces the relevant part of the page and displays an accessible toast.

### Wishlist

The wishlist uses one unique item per product. Toggling a heart either creates the wishlist item or removes it. Ownership checks prevent one customer from modifying another customer's wishlist.

### Checkout

`CheckoutRequest` validates the shipping name, email, and address. `CheckoutController@store` then performs the order in one database transaction:

1. Load the customer's current cart items.
2. Lock every selected product row.
3. Recheck active status and stock on the server.
4. Create the order and a unique order number.
5. Create snapshot order items.
6. Decrease each product's inventory.
7. Calculate and store the total.
8. Clear the cart only after the order succeeds.

If any check fails, Laravel rolls back the transaction, so a partial order or incorrect stock change is not saved.

## 6. Administration dashboard

The admin pages use `resources/views/layouts/admin.blade.php`, `resources/css/admin.css`, and `resources/js/admin.js`. They are isolated from the storefront layout and assets while sharing the same brand identity.

### Dashboard

`AdminDashboardController` calculates product, customer, order, sales, stock, and message summaries. Recent data is eager-loaded to avoid repeated database queries.

### Products

`AdminProductController` provides list, create, view, edit, status, and delete actions.

- `StoreAdminProductRequest` and `UpdateAdminProductRequest` validate every field.
- Uploaded images are validated and stored on Laravel's public disk.
- The legacy category text and new `category_id` stay synchronized.
- Deletion is soft deletion.
- Replaced admin-uploaded images are cleaned up only when no longer used.
- The mobile list changes into labelled cards so all actions remain reachable.

### Categories

`AdminCategoryController` creates and edits categories. It synchronizes the readable category value on related products. A category cannot be deleted while any active, inactive, or soft-deleted product still refers to it.

### Orders

`AdminOrderController` searches and displays orders with their customers and snapshot items. Administrators can move an order through the allow-listed states: `pending`, `processing`, `completed`, and `cancelled`.

### Customers

`AdminCustomerController` lists only customer accounts and deliberately selects safe public fields rather than passwords or remember tokens. The details page shows the customer's order history.

### Messages

`AdminMessageController` lists contact submissions, displays the full message, and changes its state to `unread`, `read`, or `replied`.

## 7. Validation and Form Requests

Submitted values are validated in classes under `app/Http/Requests` instead of being mixed into the view.

These classes provide two boundaries:

- `authorize()` decides whether the current user may perform the request.
- `rules()` defines accepted fields, lengths, formats, file types, numeric ranges, and status allow-lists.

Controllers use `$request->validated()` or `$request->safe()` so unexpected browser input is not automatically written to the database.

## 8. Frontend events

The application is event-driven in the browser. JavaScript listens for user events and then updates the interface or calls a Laravel endpoint.

| Event | Browser behavior | Backend result |
|---|---|---|
| Search input | Debounces typing and requests matching products | Active products are queried and rendered as a partial |
| Sort change | Re-runs the product request with an allowed sort value | Results return in the selected order |
| Cart button click | Sends a Fetch request with CSRF protection | Cart item and totals are saved and returned as JSON |
| Wishlist heart click | Toggles the selected state immediately after success | Wishlist row is created or removed |
| Quantity change | Sends the requested quantity | Ownership and stock are checked before saving |
| Admin status change | Sends a `PATCH` request | Validated product, order, or message status is saved |
| Mobile menu click or Escape | Changes the drawer state and ARIA attributes | No database request is required |

The JavaScript uses `data-*` attributes as hooks instead of depending on visual CSS class names. This keeps behavior separate from presentation.

## 9. Views and layouts

Storefront pages extend `resources/views/layouts/app.blade.php`. Admin pages extend `resources/views/layouts/admin.blade.php`.

Reusable fragments are stored in partial directories. For example, catalogue, cart, wishlist, and admin list markup can be rendered both on the first page request and inside a later JSON response.

Blade escapes `{{ ... }}` output by default. This prevents saved contact messages, names, and product values from being treated as executable HTML.

## 10. Database migrations and seeders

Migrations under `database/migrations` are the ordered history of the database structure. The admin extension uses new additive migrations rather than modifying the original migrations.

`DatabaseSeeder` creates the demonstration customer, cart, wishlist, categories, and ten perfume products. It also calls `AdminUserSeeder`, which creates the administrator.

For a new empty database:

```powershell
php artisan migrate --seed
```

For an existing database, use `php artisan migrate` and back up the data first. Do not use `migrate:fresh` because it drops all tables.

## 11. Security decisions

- Laravel session authentication is used instead of trusting browser-only state.
- CSRF tokens protect every state-changing form and Fetch request.
- Admin middleware protects every admin URL and mutation.
- Customer item ownership is checked on the server.
- Login attempts are rate-limited per username and IP address.
- Product uploads are restricted by image type and size.
- Status values and sort fields come from explicit allow-lists.
- Transactions and row locks prevent partial checkout and overselling.
- Password and remember-token fields are hidden by the `User` model.
- Admin responses use no-cache headers to reduce protected-page display after logout.

## 12. Automated tests

Tests are located under `tests/Feature` and `tests/Unit`. Feature tests make requests to the application and assert the response, authentication state, validation errors, and database changes.

The testing environment uses SQLite in memory, so this command does not erase the normal MySQL database:

```powershell
php artisan test
```

Important coverage includes:

- Valid and invalid login behavior
- Guest, customer, and administrator access boundaries
- Cart and wishlist ownership
- Active/inactive product visibility
- Admin product, category, order, and message operations
- Image upload validation
- Checkout totals, inventory changes, snapshots, and rollback behavior
- Contact-message validation and output escaping

## 13. How to make common changes

### Add a new admin page

1. Add a named route inside the existing admin route group in `routes/web.php`.
2. Create a controller under `app/Http/Controllers/Admin`.
3. Create a Form Request for submitted values.
4. Add a Blade view under `resources/views/admin`.
5. Add the navigation link to `resources/views/layouts/admin.blade.php`.
6. Add feature tests for guest, customer, administrator, validation, and persistence behavior.

### Add a database field

1. Create a new migration; do not edit a migration that has already run on another database.
2. Update the model's fillable attributes or casts when necessary.
3. Add validation to the relevant Form Request.
4. Update the controller and Blade form/display.
5. Update factories and tests.
6. Run `php artisan migrate`.

### Add another order status

1. Add the value to `Order::STATUSES`.
2. Update the admin status selector and any label styling.
3. Decide whether it affects dashboard sales/completion calculations.
4. Add tests for the transition and invalid values.

### Change storefront styling

Edit `resources/css/app.css`, then run `npm run dev` during development or `npm run build` before deployment.

### Change admin styling

Edit `resources/css/admin.css`. Admin interaction code is in `resources/js/admin.js`.

## 14. Presentation summary

The central design idea is that the browser creates events, Laravel enforces the trusted rules, and MySQL stores the result. JavaScript improves responsiveness, but authorization, stock validation, ownership, prices, order totals, and status allow-lists are always checked again on the server. This separation is what makes the project both event-driven and safe.
