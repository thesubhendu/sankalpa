I’m building a gamified productivity app in Laravel 12 using Filament PHP 3.3

The app name is **Sankalpa**.

Please generate the MVP backend structure with Eloquent models, migrations, policies, and Filament resources for the following:

---

## 🎯 Core Concepts:

- **Goal**
  - id, title, description, start_date, end_date, difficulty_level (enum: easy, medium, hard), user_id (FK)
  
- **Milestone** (Optional, only for Big Goals)
  - id, goal_id (FK), title, description, due_date

- **Task**
  - id, milestone_id (FK, nullable), goal_id (FK), title, description, due_date, status (enum: pending, in_progress, done), points, user_id (FK)

- **TaskCompletion**
  - id, task_id, completed_at, notes (optional)

- **PointTransaction**
  - id, user_id, source (enum: task_completion, bonus, streak, penalty), amount, description, created_at

---

## 🔧 Requirements:

1. Generate Models, Migrations, and Filament Resources for these entities.

2. For the **Dashboard**, create a custom `TaskProgressWidget` that:
   - Shows today’s pending task (just one)
   - Shows current streak
   - Shows total points earned this week
   - Shows % progress on current active goal

3. Add a **service class** `GoalProgressService` to calculate:
   - Completion % of a goal
   - Total points from its completed tasks
   - Number of remaining tasks

4. Keep everything clean, no overengineering, just core relations and resources to get this MVP running.

Note: The Dashboard and Filament are already installed and working, and the Admin user is created.

---
Generate the code in Laravel 12 and Filament 3 style. Add policies if needed, and set up relations properly.