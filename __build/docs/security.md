# Security Documentation

**Version:** 0.1
**Date:** 2025-04-08

## 1. Overall Approach

Security will be implemented by leveraging Laravel's built-in features, adhering to secure coding practices, and configuring the hosting environment securely. The strategy focuses on multiple layers of protection.

## 2. Authentication

* **Methods (MVP):** Email/Password, Social Login (via Laravel Socialite).
* **Methods (Post-MVP):** Multi-Factor Authentication (MFA/2FA).
* **Security Measures:**
    * **Password Hashing:** Secure, irreversible hashing using Bcrypt or Argon2 (Laravel default).
    * **Password Complexity:** Minimum complexity rules (e.g., length, mixed case, numbers) enforced via Laravel Validation during registration and password reset.
    * **CSRF Protection:** Enabled by default in Laravel, protecting against cross-site request forgery.
    * **Session Protection:** Secure session management handled by Laravel defaults (e.g., session fixation prevention). Session timeout duration configurable.
    * **HTTPS:** All traffic must be served over HTTPS (SSL/TLS encryption). Handled via managed SSL certificates from Render.
    * **Rate Limiting:** Applied to authentication routes (login, registration, password reset) using Laravel's `throttle` middleware to prevent brute-force attacks.

## 3. Authorization

* **Strategy:** Role-Based Access Control (RBAC).
* **Implementation:** Using Laravel Gates and Policies.
* **Roles Defined:**
    * `Player`
    * `Coach/Team Admin`
    * `League Admin` (Superuser role)
* **Key Permission Rules:**
    * Access to specific data and actions strictly controlled based on user role and context (e.g., team membership, management scope).
    * Players can manage own account settings but cannot edit ringette profile data (name, jersey, etc.). Access restricted primarily to viewing own stats/clips (if subscribed) and general league info.
    * Coach/Team Admins manage their single assigned team (`users.managed_team_id`): roster, stats (historical edits allowed), schedules, game data, clips.
    * League Admins have broad permissions to manage Associations, Leagues, Seasons, assign Coaches, and view/potentially manage data across multiple teams/leagues.
    * Access to paid features (e.g., player viewing clips) requires verification of an active subscription status (via Laravel Cashier).

## 4. Data Encryption (at Rest)

* **Passwords:** Handled via secure hashing (irreversible).
* **Other Data:** No other specific data fields identified as requiring explicit database-level encryption for MVP. Standard database security practices apply.

## 5. Input Validation & Sanitization

* **Requirement:** Mandatory backend validation for ALL user-provided input (forms, URL parameters, etc.).
* **Implementation:** Using Laravel's Validation component extensively (in controllers or Form Requests). Define strict rules for data types, formats, lengths, existence in related tables, etc.
* **Purpose:** Ensure data integrity, prevent storage of invalid data, mitigate risks of XSS (by ensuring stored data is clean, complementing Vue's output escaping), and support SQL Injection prevention (by using validated data with Eloquent).

## 6. Other Security Considerations

* **Dependencies:** Regularly update backend (Composer) and frontend (NPM) dependencies to patch known vulnerabilities.
* **Hosting:** Utilize secure configurations provided by Render (e.g., firewall, managed SSL).
* **Error Reporting:** Configure Sentry to avoid leaking sensitive stack trace information in production error pages (Laravel handles this well in production mode).