# Frontend Documentation

**Version:** 0.1
**Date:** 2025-04-08

## 1. Core Technologies

* **UI Framework:** Vue.js (v3.5+)
* **Architecture:** Server-Driven Single Page Application (SPA) via Inertia.js (v2.0.6). The backend framework will directly serve page data to Vue components.
* **UI Component Library:** shadcn-vue (Utilizing Radix Vue primitives). Provides unstyled, accessible components.
* **Styling:** Tailwind CSS (Utility-first CSS framework).

## 2. Theming

* Styling will be primarily managed through utility classes in the markup.
* A central theme (colors, fonts, spacing, etc.) will be defined in `tailwind.config.js`.
* `shadcn-vue` components will be themed using CSS variables derived from the Tailwind configuration, allowing for easy modification of the application's look and feel.
* No specific color palette or typography is defined at this stage; defaults will be used initially, configured for easy updating.

## 3. Navigation

* **Primary Navigation:** A fixed, persistent left sidebar containing links to main application sections (e.g., "My Stats", "My Team", "Games", "Teams", "Game Schedules"). Menu items will be dynamic based on user role (Player, Coach/Admin).
* **Secondary Navigation:** Tabs may be used within specific complex views (e.g., navigating between Roster, Schedule, and Stats on a Team page) to organize content.

## 4. Key Forms & Interactions

* **User Login:** Standard email and password form.
* **Coach - Send Clip:** Simple form likely within a modal or dedicated view to select a player from their team and add a text note associated with a clip.
* **Admin - Add Player:** Form to add a player to a team roster (likely simple fields like name, number, etc.).
* **Admin - Update Player Stats:** Mechanism to adjust player statistics (details TBD, likely simple fields).
* **Admin - Import/Manage Game:**
    * **Type:** Multi-step, manual data entry process.
    * **Flow:**
        1.  Select Home/Away teams & input final scores.
        2.  System generates a draft game record.
        3.  Admin adds players involved in the game.
        4.  Admin inputs player stats for the game (e.g., shots, goals) into fields (likely within a table).
        5.  Admin inputs a YouTube video URL.
        6.  Admin explicitly publishes the game to make it visible.
    * **Complexity:** No file uploads. Simple validation rules initially.
    * **Persistence Requirement:** Form state (all entered data) **must** be preserved automatically (e.g., saved as a draft to the backend) if the admin navigates away before publishing, allowing them to resume later.
* **Search/Filtering:** Input fields may be present above tables to allow users to filter game lists, team lists, player lists, etc.

## 5. State Management Considerations

* Most simple forms can rely on local Vue component state (`ref`, `reactive`).
* The "Import Game" form's persistence requirement necessitates a more robust solution. Potential approaches include:
    * Auto-saving draft data to the backend API periodically or on blur/navigation events.
    * Using a global state management library (like Pinia) with persistence to `localStorage` (less ideal for multi-device/session consistency but simpler initially).
    * *(Decision to be finalized based on backend capabilities and overall state management strategy)*.