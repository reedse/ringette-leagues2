# Backend Documentation

**Version:** 0.1
**Date:** 2025-04-08

## 1. Core Technologies

* **Framework:** Laravel (v11 - targeting latest stable features) - PHP
* **Database:** MySQL (v8.4.x requested, planning based on v8.x general availability)
* **Architecture:** Monolithic web application. Leverages Inertia.js to serve Vue.js frontend components directly from Laravel controllers, minimizing the need for a separate API layer for the primary web interface.

## 2. Database Interaction

* **ORM:** Laravel Eloquent ORM will be used for all database interactions (queries, relationships, data manipulation).
* **Schema Management:** Database schema changes will be managed using Laravel Migrations.
* **Seeding:** Laravel Seeders will be used for populating necessary initial data (e.g., roles, potentially initial admin user).

## 3. Authentication & Authorization

* **Authentication Strategy (MVP):**
    * **Email/Password:** Secure registration and login using hashed passwords.
    * **Social Login:** Integration via `laravel/socialite` (Specific providers TBD, e.g., Google).
    * Implemented using a Laravel starter kit (**Breeze** or **Jetstream** - TBD) adapted for Inertia.js.
* **Authentication Strategy (Post-MVP):**
    * **Multi-Factor Authentication (MFA/2FA):** Planned addition for enhanced security.
* **Authorization:** Laravel's Gates and Policies will be used to manage user permissions based on roles (e.g., Player, Coach/Admin).

## 4. API Design

* **Primary Interface:** Server-driven via Inertia.js. Laravel controllers return Inertia responses with page components and necessary data props.
* **External APIs:** No general-purpose external API (REST/GraphQL) is planned for the MVP for consumption by third parties or other frontends.
* **Webhooks:** Dedicated webhook endpoints will be implemented to receive data from third-party services:
    * **Stripe:** For payment events (e.g., subscription updates, successful payments). Likely handled via Laravel Cashier's built-in webhook controller.

## 5. Third-Party Integrations

* **Payments:**
    * **Service:** Stripe
    * **Implementation:** Using `laravel/cashier` package to manage player membership subscriptions/payments. Requires Stripe API keys, product/price setup, webhook configuration, and frontend integration (e.g., Stripe Elements/Checkout).
* **Email:**
    * **Purpose:** Transactional emails (e.g., welcome emails, password resets, potentially notifications).
    * **Implementation:** Using Laravel's built-in `Mail` facade and Mailable classes.
    * **Production Driver:** To be determined (e.g., Mailgun, Postmark, Amazon SES). Requires appropriate API keys/credentials and service configuration. Local development will likely use `mailpit` or log driver.

## 6. Key Backend Responsibilities

* Handling user authentication and session management.
* Managing database CRUD (Create, Read, Update, Delete) operations for all entities (Users, Teams, Players, Games, Stats, Clips, Schedules, etc.).
* Enforcing authorization rules.
* Processing payments and managing subscription status via Stripe/Cashier.
* Handling data import/management logic initiated by Admins.
* Sending transactional emails.
* Responding to Inertia requests with appropriate page components and data.
* Handling incoming webhooks (initially from Stripe).