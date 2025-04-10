# User Flow Documentation

**Version:** 0.1
**Date:** 2025-04-08

## 1. User Onboarding

* **Registration:**
    * Users navigate to Register page.
    * Input: Name, Email, Password, Password Confirmation.
    * Select Role: 'Player' or 'Coach' (Radio button/dropdown).
    * If 'Player': Select Team (Dropdown), Input Jersey Number.
    * If 'Coach': No further input needed at signup.
    * Submit.
    * Backend creates `user`, assigns basic 'Player'/'Coach' role (`role_user`), attempts player linking, sends verification email.
* **Player Linking (on Registration):**
    * System searches for unique `players` record where `team_id`, `jersey_number` match input, and `user_id` is NULL.
    * If unique match found: Link `players.user_id = user.id`.
    * If **no** match found: **Create a new** `players` record using registration data (name from user, team\_id, jersey\_number) and link `user_id`.
    * If non-unique match found: Error state (requires admin intervention - exact handling TBD, maybe prevent linking and notify user/admin).
* **Coach Promotion:**
    * A `League Admin` manually assigns the 'Coach/Admin' role via `role_user` to a registered 'Coach' user.
    * The `League Admin` also sets the `users.managed_team_id` to link the Coach/Admin to their designated team.
* **Login:**
    * Standard Email/Password form.
    * Social Login option (e.g., Google via Socialite).
* **Redirects:** After successful registration/verification/login, redirect user to their role-appropriate dashboard (e.g., `/my-stats`, `/my-team`, `/admin-dashboard`).

## 2. Core User Journeys

* **Player:** Login -> View Personal Stats -> View Game Schedule/Results -> View Specific Game Details -> View Received Clips & Notes -> Logout.
* **Coach/Admin (Game Mgmt):** Login -> Find Game (or Import) -> Enter/Edit Scores/Video URL -> Add/Select Players -> Enter Player Stats -> Add Penalties -> Save/Publish Game -> Logout.
* **Coach/Admin (Clip Mgmt):** Login -> Select Game/Video -> Define Clip (Title, Start/End) -> Select Player(s) -> Add Note -> Send Clip -> Logout.
* **League Admin:** Login -> Manage Associations/Leagues/Seasons -> Assign/Promote Coaches -> View Overall Data -> Logout.

## 3. Page Interactions (Examples)

* **Tables (General):** Rows often link to detail pages (e.g., Game row -> Game Details). Admin tables may have Edit/Delete buttons.
* **Game Details (Admin View):**
    * 'Add Penalty' button opens modal form (select player, penalty code, minutes).
    * 'Edit Stats' button enables inline editing in the player stats table.
    * 'Save Stats' button persists changes made via inline editing.
* **Clips Page (Coach View):**
    * 'Create Clip' action (button?).
    * 'Send' action per clip opens a modal.
    * Send Clip Modal: Search/select players (from team roster), input `coach_note`, confirm send.
* **Forms:** Login, Registration, Admin Game Import, Add Penalty Modal, Send Clip Modal, Filter/Search inputs above tables.
* **Navigation:** Fixed Left Sidebar (role-based items), potential Tabs within complex pages.

## 4. Error Handling (in Flow)

* **Input Validation:** Inline error messages displayed next to invalid fields (forms, modals, inline edits). Prevent save/submit until errors are corrected.
* **Action Failures (Save/Send):** Toast notifications used to inform user of failure (e.g., "Save failed, please try again"). UI state generally preserved to allow retry.

## 5. Edge Cases (MVP Handling)

* **Connectivity Issues:** UI should indicate loss of connection. Backend draft saving (esp. for game import) mitigates data loss.
* **Incomplete/Draft Data:** Use `games.status = 'draft'`. Draft data generally not visible publicly/to players. Handle potential calculation errors (e.g., display N/A) if source data is missing.
* **Expired Sessions:** Standard redirect to login page. Backend draft saving minimizes data loss for critical forms.
* **Large Data Volumes:** Backend pagination **required** for all potentially large lists (games, players, teams, clips, stats). Frontend tables must support pagination controls.
* **Empty States:** Design and implement clear, user-friendly messages/UI for pages or sections with no data.

## 6. Alternative Flows

* **Guest/Public Access:** Yes. Unauthenticated users can view League Standings, Game Schedules, and Team Lists. Access to stats, clips, and user-specific pages requires login. Implemented via public routes and conditional rendering.

## 7. User Permissions (Flow Summary)

* **Player:** Primarily views own stats, clips, general league info. Manages own account.
* **Coach/Team Admin:** Manages assigned team's operational data (roster, schedule, game stats/penalties, clips).
* **League Admin:** Oversees structure (associations, leagues, seasons), manages coach roles/assignments, potentially users.

## 8. Notifications (MVP)

* **Real-time:** WebSocket notification to player when a clip is sent to them.
* **Email:** Standard transactional emails only (registration confirmation, password reset).
* **In-App:** Notification center deferred post-MVP.