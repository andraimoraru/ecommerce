A lightweight, server-side rendered e-commerce platform built with PHP 8, MySQL, and a custom MVC architecture. The project was developed as a final-year computing artefact to explore how a modular PHP application can support small business e-commerce workflows without relying on a heavy CMS or plugin-based framework.

About The Project
This artefact implements a custom online jewellery store with storefront browsing, customer accounts, cart and checkout functionality, Stripe payment processing, Royal Mail Click & Drop shipment creation, and an administration area for managing store content.

The application was designed around three main goals:

Build a lightweight server-rendered alternative to plugin-heavy e-commerce platforms.
Keep the architecture modular, readable, and maintainable.
Integrate external services such as Stripe and Royal Mail through dedicated service classes.
Key Features
Server-side rendered storefront using PHP views.
Custom MVC routing, controllers, models, middleware, and layouts.
Product catalogue with categories, product images, stock-aware listings, and search.
Shopping cart with quantity limits based on available inventory.
Checkout flow with shipping and billing address handling.
Stripe Checkout integration with signed webhook validation.
Payment event audit logging using the payment_events table.
Customer account area with saved addresses and order history.
Admin dashboard for products, categories, orders, customers, blog posts, and marketing settings.
Royal Mail Click & Drop shipment creation for paid orders.
Blog module with public posts and admin management.
Friendly flash messages, confirmation dialogs, custom 404 pages, and restricted-access handling.
Responsive storefront and admin interface.
Built With
PHP 8+
MySQL / MariaDB
PDO prepared statements
Composer PSR-4 autoloading
vlucas/phpdotenv
PHPUnit
HTML, CSS, and vanilla JavaScript
Stripe API
Royal Mail Click & Drop API
Architecture Overview
The platform follows a custom MVC structure:

app/
  config/       Environment-backed configuration
  Core/         Router, request/response helpers, middleware, database, base controller
  Controllers/  Storefront, account, checkout, webhook, and admin request handlers
  Models/       PDO-backed database access for products, orders, users, carts, payments
  Services/     External API integrations for Stripe and Royal Mail Click & Drop
  Views/        Server-rendered PHP templates and layouts

public/
  assets/       CSS and JavaScript
  uploads/      Product image uploads
  index.php     Front controller
The request lifecycle follows a front-controller pattern:

Browser request
  -> public/index.php
  -> App\Core\Router
  -> Middleware checks where required
  -> Controller action
  -> Model and/or Service classes
  -> Server-rendered View
  -> HTML response
Database Scope
The database is designed around relational e-commerce entities, including:

users
products
categories
inventory
product_images
product_categories
carts
cart_items
orders
order_items
order_addresses
order_payments
payment_events
order_shipments
addresses
user_addresses
blog_posts
marketing_settings
The order flow intentionally stores transactional snapshots, such as product names, SKUs, prices, and customer address details, so historical order records remain accurate even if catalogue or account data changes later.

Installation
Requirements
PHP 8.0 or newer
Composer
MySQL or MariaDB
Apache, XAMPP, MAMP, or the PHP built-in development server
cURL enabled for Stripe and Royal Mail API requests
Clone The Repository
git clone https://github.com/andraimoraru/ecommerce.git
cd ecommerce
composer install
Configure Environment Variables
Create a .env file from the example:

cp .env.example .env
Then update the values for your local environment:

APP_ENV=local
APP_URL=http://localhost:8000
SITE_NAME="Modular E-commerce"

DB_HOST=127.0.0.1
DB_NAME=modular_ecom
DB_USER=root
DB_PASS=

STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=
ROYAL_MAIL_CLICK_DROP_API_KEY=
Run Locally
php -S localhost:8000 -t public
Open:

http://localhost:8000
Testing
Run the PHPUnit test suite:

composer test
Run PHP syntax checks across the application:

composer lint
External Integrations
Stripe
Stripe is handled through app/Services/StripeGateway.php.

Implemented payment features include:

Checkout Session creation.
Checkout Session retrieval after redirect.
Signed webhook verification.
Payment event audit logging.
Idempotency check using provider_event_id.
order_payments updates.
Order status update to PAID after confirmed payment.
Royal Mail Click & Drop
Royal Mail is handled through app/Services/RoyalMailClickDropService.php.

The current implementation creates shipments in Click & Drop and stores shipment metadata in order_shipments. It does not generate or store Royal Mail label PDFs inside the application; label management remains in the Royal Mail account.

Admin Area
The admin area includes:

Product management.
Category management.
Order viewing and editing.
Royal Mail shipment creation.
Customer management.
Blog management.
Marketing settings for Instagram and Facebook profile details.
Admin access is protected using session-based authentication and role checks through middleware.

Academic Context
This project was developed as a computing artefact exploring:

Lightweight server-side rendering.
Custom MVC architecture.
Relational database design.
Secure payment processing.
Modular API integration.
Performance-conscious implementation through pagination, limited result sets, selective lazy loading, and reduced dependency usage.
Project Status
The artefact implements the main storefront, customer, checkout, payment, shipping, and administration workflows. Some production-hardening tasks remain suitable for future development, including automated image compression, static asset cache headers, a minified asset build step, and broader automated test coverage.

License
This project is licensed under the MIT License.
