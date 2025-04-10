# API Communication & Interaction Documentation

**Version:** 0.1
**Date:** 2025-04-08

## 1. Overall Approach

* The application primarily uses a server-driven architecture with **Laravel** and **Inertia.js**.
* Frontend interactions (page loads, form submissions) trigger requests to Laravel controllers.
* Controllers handle business logic, database interaction, and return Inertia responses containing the necessary Vue page component and data props.
* Traditional REST/GraphQL APIs consumed directly by the frontend are minimized.

## 2. Key Endpoints / Controller Actions (Illustrative List)

Laravel routes and controller actions will handle interactions such as:

* **Read Operations (GET):**
    * `/dashboard`, `/my-stats`, `/my-clips` (Role-dependent views)
    * `/games`, `/games/{game}`
    * `/teams`, `/teams/{team}`
    * `/players`, `/players/{player}`
    * `/schedule`
    * `/admin/games/import` (View form)
    * `/settings/subscription` (View billing)
* **Write Operations (POST/PUT/PATCH/DELETE):**
    * Auth routes: `/login`, `/register`, `/logout`, `/password/reset`, etc.
    * Admin: `/admin/games` (Submit Import), `/admin/games/{game}` (Update), `/admin/teams/{team}/players` (Add Player), `/admin/players/{player}` (Update Player)
    * Coach: `/coach/clips/{clip}/send` (Share Clip)
    * Payments: `/subscriptions` (Create), `/subscriptions/{subscription}` (Cancel) (Handled via Laravel Cashier routes/controllers)
* **Webhooks (POST):**
    * `/webhooks/stripe` (Handles Stripe events, likely via Cashier)

*(This list is representative; specific routes defined in `routes/web.php`)*

## 3. Error Handling Strategy

* **Validation Errors:** Handled by Laravel's validator. Errors are flashed to the session and made available to Inertia frontend components via the `errors` prop for inline display near form fields.
* **General Errors/Success Messages:** Backend flashes messages to the session (e.g., `session()->flash('success', 'Game imported!')`). Inertia shares these flash messages as props, allowing the frontend to display toast notifications or alerts.
* **HTTP Errors (4xx/5xx):** Standard HTTP error responses (404 Not Found, 403 Forbidden, 500 Server Error, etc.) will be intercepted, and Inertia will render corresponding custom Vue error page components for a user-friendly experience.

## 4. Rate Limiting

* **Requirement:** Yes, for MVP.
* **Implementation:** Using Laravel's built-in `throttle` middleware.
* **Scope:** Applied to sensitive endpoints, particularly authentication routes (login, registration, password reset requests) to prevent brute-force attacks and abuse. Specific limits (e.g., 5-10 requests per minute per IP) to be configured.

## 5. Real-time Communication (WebSockets)

* **Requirement:** Yes, for MVP.
* **Use Case:** Instantly notify a player when a coach sends them a video clip.
* **Technology Stack:**
    * **Backend:** Laravel Broadcasting system (defining broadcast Events), Queue worker (recommended).
    * **WebSocket Server:** E.g., **Laravel Reverb** (self-hosted, first-party), Pusher (cloud), Ably (cloud). (Reverb preferred if suitable).
    * **Frontend:** **Laravel Echo** configured to connect to the WebSocket server and listen on private channels for authenticated users.
* **Functionality:** When a coach sends a clip, the backend dispatches a broadcast event. The WebSocket server pushes this event to the relevant authenticated player's browser via Echo, triggering a UI notification (e.g., toast).