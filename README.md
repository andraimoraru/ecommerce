# Modular Lightweight E-Commerce Platform

A lightweight, server-side rendered e-commerce platform built with PHP 8, MySQL, and a custom MVC architecture.

This project was developed as a final-year Computing artefact to explore how a modular PHP application can support small business e-commerce workflows without relying on a heavy CMS or plugin-based framework.

---

## About The Project

This artefact implements a custom online jewellery store featuring storefront browsing, customer accounts, shopping cart and checkout functionality, Stripe payment processing, Royal Mail Click & Drop shipment creation, and an administration area for managing products, orders, content, and marketing settings.

The application was designed around three primary objectives:

* Build a lightweight server-rendered alternative to plugin-heavy e-commerce platforms.
* Maintain a modular, readable, and maintainable architecture.
* Integrate external services such as Stripe and Royal Mail through dedicated service classes.

---

## Features

### Storefront

* Product catalogue browsing
* Category filtering
* Product image galleries
* Stock-aware product listings
* Product search
* Responsive user interface

### Customer Features

* User registration and login
* Customer account dashboard
* Saved addresses
* Order history
* Order details

### Shopping & Checkout

* Shopping cart management
* Inventory-aware quantity limits
* Shipping and billing address handling
* Checkout workflow
* Stripe Checkout integration

### Payments

* Stripe Checkout Session creation
* Signed webhook verification
* Payment event audit logging
* Duplicate webhook prevention
* Order payment status updates

### Shipping

* Royal Mail Click & Drop integration
* Shipment creation from paid orders
* Shipment tracking metadata storage

### Administration

* Product management
* Category management
* Customer management
* Order management
* Blog management
* Marketing settings management
* Shipment creation

### Security

* Password hashing
* Session-based authentication
* Administrator access control
* PDO prepared statements
* Stripe webhook validation

### User Experience

* Flash messages
* Confirmation dialogs
* Custom 404 pages
* Restricted-access handling
* Responsive storefront and administration interface

---

## Technology Stack

### Backend

* PHP 8+
* MySQL / MariaDB
* PDO Prepared Statements
* Composer
* PHPUnit

### Frontend

* HTML5
* CSS3
* Vanilla JavaScript

### Third-Party Services

* Stripe API
* Royal Mail Click & Drop API

### Development Tools

* XAMPP / MAMP
* Git
* GitHub
* Visual Studio Code

---

## Architecture Overview

The platform follows a custom MVC architecture:

```text
app/
├── Config/        Environment-backed configuration
├── Core/          Router, middleware, database, request/response helpers
├── Controllers/   Storefront, account, checkout, webhook and admin handlers
├── Models/        Database access and domain entities
├── Services/      Stripe and Royal Mail integrations
└── Views/         Server-rendered templates

public/
├── assets/        CSS and JavaScript
├── uploads/       Product images
└── index.php      Front controller
```

### Request Lifecycle

```text
Browser Request
      │
      ▼
public/index.php
      │
      ▼
Router
      │
      ▼
Middleware (if required)
      │
      ▼
Controller
      │
      ├── Models
      └── Services
      │
      ▼
View
      │
      ▼
HTML Response
```

---

## Database Design

The database is based on relational e-commerce entities including:

### Core Tables

* users
* products
* categories
* inventory
* carts
* cart_items
* orders
* order_items
* order_payments
* payment_events
* order_shipments
* addresses

### Supporting Tables

* product_images
* product_categories
* user_addresses
* order_addresses
* blog_posts
* marketing_settings

The order workflow intentionally stores transactional snapshots such as product names, SKUs, prices, and customer address details. This ensures historical order records remain accurate even if product or customer information changes later.

---

## Installation

### Requirements

* PHP 8.0+
* Composer
* MySQL or MariaDB
* Apache, XAMPP, MAMP, or PHP built-in server
* cURL extension enabled

### Clone Repository

```bash
git clone https://github.com/andraimoraru/ecommerce.git
cd ecommerce
composer install
```

### Configure Environment Variables

Create a `.env` file:

```env
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
```

### Run Locally

```bash
php -S localhost:8000 -t public
```

Open:

```text
http://localhost:8000
```

---

## Testing

Run PHPUnit:

```bash
composer test
```

Run syntax checks:

```bash
composer lint
```

---

## External Integrations

### Stripe

Stripe functionality is implemented through:

```text
app/Services/StripeGateway.php
```

Implemented features include:

* Checkout Session creation
* Checkout Session retrieval
* Signed webhook verification
* Payment event audit logging
* Idempotency checks using `provider_event_id`
* Payment status updates
* Automatic order status updates

### Royal Mail Click & Drop

Royal Mail integration is implemented through:

```text
app/Services/RoyalMailClickDropService.php
```

Implemented features include:

* Authenticated API communication
* Shipment creation
* Shipment metadata storage
* Shipment status tracking

> Note: The application creates shipments within Click & Drop but does not generate or store Royal Mail label PDFs. Label management remains within the Royal Mail platform.

---

## Admin Area

The administration interface provides:

* Product management
* Category management
* Order management
* Customer management
* Blog management
* Marketing settings management
* Royal Mail shipment creation

Administrative functionality is protected through session-based authentication and role-based middleware checks.

---

## Academic Context

This project was developed as a final-year Computing artefact investigating:

* Lightweight server-side rendering
* Custom MVC architecture
* Relational database design
* Secure payment processing
* Modular API integration
* Performance-conscious implementation

The project evaluates whether a modular PHP architecture can provide a maintainable and extensible alternative to plugin-heavy SME e-commerce platforms.

---

## Future Improvements

Potential future enhancements include:

* Social media API publishing
* Analytics dashboard
* Multi-currency support
* Advanced search and filtering
* Automated image optimisation
* Static asset caching
* Asset minification pipeline
* Additional shipping providers
* Expanded automated test coverage

---

## Author

**Andra Moraru**

BSc Computing – Arden University

GitHub: https://github.com/andraimoraru

---

## License

This project is licensed under the MIT License.
