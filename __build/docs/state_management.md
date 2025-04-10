# State Management Documentation

**Version:** 0.1
**Date:** 2025-04-08

## 1. Local State

* State confined to individual Vue components (e.g., form input values before submission, UI element visibility like modal open/close status) will be managed using Vue 3's standard reactivity system (`ref()`, `reactive()`).

## 2. Global State

* **No dedicated global state management library** (e.g., Pinia) will be implemented for the MVP to maintain simplicity.
* State needed across multiple components will primarily be handled by:
    * Passing data down through component **props**, originating from data provided by the Laravel backend via Inertia.
    * Utilizing **Inertia.js's "Shared Data"** feature for globally accessible data pushed from the server with each response (e.g., authenticated user information, roles/permissions, flash messages).

## 3. Server State & Data Fetching

* Initial data required for page rendering will be provided directly by Laravel controllers as props through Inertia.js responses.
* **Loading/Error States:** UI feedback during server interactions (form submissions, actions) will be managed using:
    * The `processing` property provided by Inertia's form helper.
    * Callbacks (`onStart`, `onProgress`, `onFinish`, `onSuccess`, `onError`) available on manual Inertia visits (`Inertia.visit`, `.post`, `.put`, etc.) to update local component state.
* **Data Updates/Re-fetching:** Data modifications will trigger server requests. Inertia will automatically update the frontend with the new props returned by the server. Partial reloads (`only: [...]` option) may be used for optimization where applicable.
* **Caching:** Relies on the server as the source of truth for data. Standard browser HTTP caching will be used for assets. No complex client-side data caching strategy is planned for the MVP.

## 4. State Persistence

* **Import Game Form Progress:** To ensure robustness and handle potentially large amounts of data entry, the state of the multi-step "Import Game" form will be persisted by saving it as a **draft to the backend database**. This could happen periodically via auto-save or when the user navigates away.
* **UI Preferences:** The user's selected **light/dark mode** preference will be persisted across sessions using the browser's `localStorage`. This preference will be applied on page load to set the appropriate CSS class on the root element.
* **Other Persistence:** Needs like remembering table filter settings are deferred post-MVP.