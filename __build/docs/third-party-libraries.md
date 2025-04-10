# Third-Party Libraries & Services Documentation

**Version:** 0.1
**Date:** 2025-04-08

## 1. Introduction

This document lists the key third-party libraries, packages, and external services (beyond the core Laravel framework, Vue.js, and MySQL database) required for the development and operation of the Ringette League Hub application, based on the features and architecture decided during planning.

## 2. Backend Dependencies (Composer Packages)

* **`laravel/cashier-stripe`**: Required for integrating Stripe payments and subscription management (Player upgrades).
* **`laravel/socialite`**: Required for handling OAuth-based social logins (e.g., Google).
* **`laravel/reverb`**: Recommended first-party package for providing the WebSocket server functionality needed for real-time broadcasting. Requires installation and running a separate server process. *(Alternative: Cloud services like Pusher with `pusher/pusher-php-server`)*.
* **`sentry/sentry-laravel`**: Required for integrating Sentry error tracking for the Laravel backend.
* **`phpunit/phpunit`**: Included by default with Laravel for unit and integration testing.

## 3. Frontend Dependencies (NPM Packages)

* **`@inertiajs/vue3`**: The core adapter enabling Inertia.js functionality within the Vue 3 application.
* **`tailwindcss`**: Utility-first CSS framework for styling.
* **`postcss`**, **`autoprefixer`**: Required build tool dependencies for Tailwind CSS.
* **`shadcn-vue`**: UI component library providing unstyled, accessible primitives (built on Radix Vue).
* **`radix-vue`**: Dependency for `shadcn-vue`, providing core headless UI primitives.
* **`lucide-vue-next`**: Icon library commonly used with `shadcn-vue`.
* **`laravel-echo`**: JavaScript library for subscribing to WebSocket channels broadcasted from the Laravel backend.
* **`pusher-js`**: Required dependency for Laravel Echo, regardless of the backend WebSocket driver used (Reverb, Pusher, Ably).
* **`@sentry/vue`**, **`@sentry/integrations`**: Required for integrating Sentry error tracking and performance monitoring for the Vue.js frontend.
* **`vue3-apexcharts`**: Recommended library for rendering interactive charts and data visualizations. *(Alternative: `vue-chartjs` with `chart.js`)*.
* **`vue`**: The core frontend framework.
* **`vite`**: The build tool used for frontend asset compilation (likely included via Laravel starter kits).

## 4. External Services (Require Accounts & Configuration)

* **Stripe:** Payment processing service. Requires an account, API keys (publishable and secret), product/price setup, and webhook configuration.
* **Google Cloud Platform (or other provider):** Required to obtain API keys/credentials for enabling Google Social Login (or other chosen providers).
* **Sentry.io:** Error tracking and performance monitoring service. Requires an account and project DSN (API key).
* **Transactional Email Service:** A service like Mailgun, Postmark, AWS SES, SendGrid is needed for reliable production email delivery. Requires an account and API keys/credentials configured in Laravel's mail settings.
* **Uptime Monitoring Service:** A service like UptimeRobot or Better Uptime. Requires an account and configuration of the production URL monitor.
* **Render.com:** The chosen hosting platform. Requires an account and configuration of various services (Web, DB, Redis, Worker, Reverb).

## 5. Notes

* Specific versions for packages will be determined during development (using latest stable versions is recommended).
* The choice of WebSocket server (Reverb vs. Pusher/Ably) should be confirmed before backend setup. Reverb is recommended for self-hosting simplicity integrated with Laravel.
* The specific production email driver needs to be chosen and configured.
* Regularly updating dependencies (via `composer update` and `npm update`) is crucial for security and bug fixes, but should be done cautiously with testing.