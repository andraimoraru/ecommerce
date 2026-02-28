# Modular Lightweight E-Commerce Platform

A modular, server-side rendered (SSR) e-commerce platform built using modern PHP (PSR-4) and MySQL, designed for small businesses seeking performance, maintainability, and clean external API integration.

Developed as part of a BSc Computing final year dissertation, this project evaluates the performance and architectural trade-offs of a lightweight modular PHP framework compared to mainstream platforms.

---

# 🎯 Project Aim

To design, implement, and evaluate a modular lightweight e-commerce application that:

Achieves efficient server-side rendering

Minimizes server resource usage

Supports clean API integration via adapter modules

Enables maintainable, scalable architecture without heavy frameworks

# 🏗 Architecture Overview

The platform follows a manual MVC architecture enhanced with modular design principles:

app/
  Core/        → Routing, Database, Base Controller
  Controllers/ → HTTP entry points
  Models/      → Data access logic
  Services/    → Business logic layer
  Modules/     → External API adapters (Payments, Shipping)
  Views/       → Server-rendered templates

Architectural Principles

PSR-4 autoloading (Composer)

Environment-based configuration (.env)

PDO with prepared statements only

Strict typing (PHP 8+)

Clear separation of concerns

Modular adapter pattern for integrations

External integrations (e.g., Stripe, Royal Mail) are designed as isolated modules under /Modules.




## Quickstart

Requirements
- PHP 8+
- Composer
- MySQL / MariaDB 
- Apache (XAMPP for local dev) or use PHP built-in server

Clone and install

```bash
git clone https://github.com/andraimoraru/ecommerce.git
cd "Computing artefact"
composer install
cp .env.example .env

```

## Run locally

PHP built-in server (quick)

```bash

php -S localhost:8000 -t public

# Open http://localhost:8000 in your browser

```
# 🧪 Testing Strategy

Test structure:

tests/
  Unit/
  Integration/

Unit tests validate isolated components (e.g., autoloading, services).

Integration tests validate multi-layer flows (e.g., checkout process).

- Run the test suite (unit + integration):

```bash
composer test
```

- You can also run PHPUnit directly:

```bash
./vendor/bin/phpunit --configuration phpunit.xml
```

# 📊 Research Evaluation Criteria

The platform will be evaluated against:

Page load time

Time To First Byte (TTFB)

Memory consumption

SQL query count

Modularity and coupling analysis

Benchmark comparisons will be made against published metrics from mainstream platforms such as Shopify and WooCommerce.

## License

MIT
