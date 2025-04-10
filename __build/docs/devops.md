# DevOps Documentation

**Version:** 0.1
**Date:** 2025-04-08

## 1. Hosting

* **Platform:** Render.com (PaaS)
* **Reasoning:** Chosen for its balance of ease of use, scalability features, predictable pricing, and strong support for the required stack (Laravel, MySQL, Redis, Queues, WebSockets via Reverb).
* **Region:** Toronto, Canada (or nearest suitable Render region).
* **Required Render Services (Initial Setup):**
    * Web Service (for Laravel application)
    * Private Network
    * Managed Database (MySQL 8.x)
    * Managed Cache/Store (Redis - for sessions, cache, queues, potentially Reverb backend)
    * Background Worker (for running `php artisan queue:work`)
    * Background Service (or similar, for running `php artisan reverb:start` WebSocket server)

## 2. Deployment (CI/CD)

* **MVP Strategy:** Continuous Deployment via Render's built-in Git integration.
    * Pushing code to the designated production branch (e.g., `main`) in the connected Git repository (GitHub/GitLab/Bitbucket) will automatically trigger a build and deployment on Render.
* **CI:** No separate automated testing pipeline (e.g., GitHub Actions running tests) configured to run *before* deployment for the MVP. Render's build process will handle composer/npm installs and frontend asset building. (Formal CI can be added post-MVP).

## 3. Monitoring

* **Error Tracking:** Sentry.io will be integrated.
    * Requires installing and configuring `sentry/sentry-laravel` (backend) and `@sentry/vue` (frontend) SDKs.
    * Sentry DSN and other relevant configurations stored as environment variables on Render.
* **Uptime Monitoring:** An external service like UptimeRobot (or similar) will be configured to monitor the production application URL (e.g., every 5 minutes) and send alerts on downtime.
* **Performance Monitoring:**
    * Basic application metrics (CPU, RAM, response time) will be available via Render's dashboard.
    *