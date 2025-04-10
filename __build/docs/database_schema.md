# Database Schema Documentation

**Version:** 0.1
**Date:** 2025-04-08

## Core Tables

### Users
- **id**: Primary Key
- **name**: User's name
- **email**: User's email (unique)
- **password**: Hashed password
- **managed_team_id**: Foreign key to Team (optional, for coaches)
- *Timestamps*

### Roles
- **id**: Primary Key
- **name**: Role name (e.g., Admin, Coach, Player)
- *Timestamps*

### role_user (pivot)
- **role_id**: Foreign key to Role
- **user_id**: Foreign key to User
- *Timestamps*

### Players
- **id**: Primary Key
- **user_id**: Foreign key to User (optional, for player accounts)
- **first_name**: Player's first name
- **last_name**: Player's last name
- **jersey_number**: Player's jersey number
- **position**: Player's position
- **date_of_birth**: Player's birth date
- *Timestamps*

### Associations
- **id**: Primary Key
- **name**: Association name
- *Timestamps*

### Leagues
- **id**: Primary Key
- **name**: League name
- **association_id**: Foreign key to Association
- *Timestamps*

### Seasons
- **id**: Primary Key
- **name**: Season name
- **start_date**: Season start date
- **end_date**: Season end date
- **league_id**: Foreign key to League
- *Timestamps*

### Teams
- **id**: Primary Key
- **name**: Team name
- **association_id**: Foreign key to Association
- **league_id**: Foreign key to League
- **season_id**: Foreign key to Season (primary season)
- *Timestamps*

### roster_entries (pivot)
- **id**: Primary Key
- **player_id**: Foreign key to Player
- **team_id**: Foreign key to Team
- **season_id**: Foreign key to Season
- *Timestamps*
- *Unique constraint*: [player_id, team_id, season_id]

### Games
- **id**: Primary Key
- **season_id**: Foreign key to Season (required)
- **league_id**: Foreign key to League (required)
- **home_team_id**: Foreign key to Team
- **away_team_id**: Foreign key to Team
- **home_score**: Home team score (nullable)
- **away_score**: Away team score (nullable)
- **status**: Game status (Scheduled, In Progress, Completed)
- **game_date_time**: Game date and time
- **location**: Game location (nullable)
- **video_url**: URL to game video (nullable)
- *Timestamps*

### PlayerGameStats
- **id**: Primary Key
- **game_id**: Foreign key to Game
- **player_id**: Foreign key to Player
- **team_id**: Foreign key to Team (player's team for this game)
- **goals**: Number of goals
- **assists**: Number of assists
- **plus_minus**: Plus/minus statistic
- *Timestamps*

### PenaltyCodes
- **id**: Primary Key
- **code**: Penalty code
- **description**: Penalty description
- **minutes**: Penalty minutes
- *Timestamps*

### GamePenalties
- **id**: Primary Key
- **game_id**: Foreign key to Game
- **player_id**: Foreign key to Player
- **team_id**: Foreign key to Team
- **penalty_code_id**: Foreign key to PenaltyCode
- **period**: Period when penalty occurred
- **time**: Time within period when penalty occurred
- *Timestamps*

### Clips
- **id**: Primary Key
- **game_id**: Foreign key to Game
- **coach_user_id**: Foreign key to User (coach who created the clip)
- **title**: Clip title
- **description**: Clip description
- **video_url**: URL to the video
- **start_time**: Start time in the video
- **end_time**: End time in the video
- *Timestamps*

### clip_player (pivot)
- **clip_id**: Foreign key to Clip
- **player_id**: Foreign key to Player
- **coach_note**: Note from coach to player
- **sent_at**: When the clip was sent to the player
- *Timestamps*

## Entity Relationships

### Users
- Has many roles (via role_user pivot)
- May have one Player profile (if they are a player)
- May manage one Team (if they are a coach)
- Creates Clips (if they are a coach)

### Roles
- Has many users (via role_user pivot)

### Players
- Belongs to one User (optional)
- Belongs to many Teams per Season (via roster_entries)
- Belongs to many Seasons (via roster_entries)
- Has many PlayerGameStats
- Has many GamePenalties
- Has many Clips shared with them (via clip_player)

### Associations
- Has many Leagues
- Has many Teams

### Leagues
- Belongs to one Association
- Has many Seasons
- Has many Teams

### Seasons
- Belongs to one League
- Has many Teams directly (primary season for team)
- Has many Teams via roster_entries (for seasonal participation)
- Has many Players via roster_entries
- Has many Games

### Teams
- Belongs to one Association
- Belongs to one League
- Belongs to one Season (primary)
- Has many Players per Season (via roster_entries)
- Has many Seasons (via roster_entries)
- Has many home Games
- Has many away Games
- Has one manager (User with Coach role)

### Games
- Belongs to one Season
- Belongs to one League
- Belongs to one home Team
- Belongs to one away Team
- Has many PlayerGameStats
- Has many GamePenalties
- Has many Clips

### PlayerGameStats
- Belongs to one Game
- Belongs to one Player
- Belongs to one Team

### PenaltyCodes
- Has many GamePenalties

### GamePenalties
- Belongs to one Game
- Belongs to one Player
- Belongs to one Team
- Belongs to one PenaltyCode

### Clips
- Belongs to one Game
- Belongs to one User (coach)
- Has many Players (via clip_player)

## Pivot Relationships
* User <-> Role (Many-to-Many via `role_user`)
* Player <-> Team <-> Season (Many-to-Many via `roster_entries`)
* Clip <-> Player (Many-to-Many via `clip_player`)

## 1. Overview

* **Database System:** MySQL (Targeting v8.x, e.g., 8.4.x)
* **Interaction:** Laravel Eloquent ORM
* **Schema Management:** Laravel Migrations
* **Backup Requirement:** Automated daily backups required for production.

## 2. Tables & Columns

*(Note: `timestamps` (`created_at`, `updated_at`) assumed unless specified otherwise. Primary Key `id` (auto-incrementing big integer) assumed unless specified otherwise.)*

* **`users`**
    * `id` (PK)
    * `name` (string)
    * `email` (string, unique)
    * `email_verified_at` (timestamp, nullable)
    * `password` (string)
    * `managed_team_id` (FK to `teams`.id, nullable - For Coach/Admin role)
    * `remember_token` (string, nullable)
    * `timestamps`
    * *(Standard Laravel Cashier fields like `stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at` will be added if using Cashier)*

* **`roles`**
    * `id` (PK)
    * `name` (string, unique - e.g., 'Player', 'Coach/Admin', 'Parent')
    * *(No timestamps needed?)*

* **`role_user`** (Pivot Table)
    * `user_id` (FK to `users`.id)
    * `role_id` (FK to `roles`.id)
    * *(Primary Key: [`user_id`, `role_id`])*

* **`players`**
    * `id` (PK)
    * `user_id` (FK to `users`.id, nullable - Links to login account if exists)
    * `first_name` (string)
    * `last_name` (string)
    * `jersey_number` (string - String to handle '00' etc.)
    * `position` (string/enum, nullable - e.g., 'Forward', 'Defence', 'Goalie')
    * `date_of_birth` (date, nullable - Optional?)
    * `timestamps`

* **`associations`** (Clubs)
    * `id` (PK)
    * `name` (string)
    * `timestamps`

* **`leagues`** (Divisions)
    * `id` (PK)
    * `name` (string - e.g., 'U14 A', 'SRRL U19 AA')
    * `timestamps`

* **`seasons`**
    * `id` (PK)
    * `name` (string - e.g., '2024-2025')
    * `start_date` (date)
    * `end_date` (date)
    * `timestamps`

* **`teams`**
    * `id` (PK)
    * `name` (string - e.g., 'U12 Dragons Red')
    * `association_id` (FK to `associations`.id)
    * `league_id` (FK to `leagues`.id)
    * *(Maybe `season_id` here too, if teams reform each season? Or rely on `roster_entries` for seasonal context? Relying on roster seems more flexible).*
    * `timestamps`

* **`roster_entries`** (Pivot linking Players to Teams per Season)
    * `id` (PK)
    * `player_id` (FK to `players`.id)
    * `team_id` (FK to `teams`.id)
    * `season_id` (FK to `seasons`.id)
    * *(Unique Constraint: [`player_id`, `team_id`, `season_id`])*
    * `timestamps`

* **`games`**
    * `id` (PK)
    * `season_id` (FK to `seasons`.id)
    * `league_id` (FK to `leagues`.id)
    * `home_team_id` (FK to `teams`.id)
    * `away_team_id` (FK to `teams`.id)
    * `game_date_time`