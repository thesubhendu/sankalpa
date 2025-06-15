# Sankalpa - Gamified Productivity App

A Laravel-based gamified productivity application built with Filament PHP for goal tracking and task management.

## 🎯 Features

- **Goal Management**: Create and track long-term goals with difficulty levels
- **Milestone Tracking**: Break down big goals into manageable milestones
- **Task Management**: Create, assign, and complete tasks with point rewards
- **Gamification**: Earn points for completing tasks and track progress
- **Dashboard Analytics**: Real-time progress tracking and statistics
- **Point System**: Track achievements with a comprehensive point transaction system

## 🚀 Quick Start

### Prerequisites
- PHP 8.3+
- Laravel 12
- SQLite/MySQL database

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd sankalpa
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed --class=SampleDataSeeder
   php artisan db:seed --class=AdminUserSeeder
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   npm run dev
   ```

## 🔑 Access

### Admin Panel (Filament)
- URL: `http://localhost:8000/admin`
- Email: `admin@sankalpa.app`
- Password: `admin123`

### Test User
- Email: `test@example.com`
- Password: `password`

## 📱 Dashboard Features

The custom dashboard widget shows:
- **Today's pending task** - Shows the most urgent task due today
- **Current streak** - Track consecutive days of task completion
- **Weekly points** - Points earned in the current week
- **Goal progress** - Completion percentage of the active goal

## 🎯 Models & Relationships

### Core Models
- **Goal**: Main objectives with start/end dates and difficulty levels
- **Milestone**: Optional sub-goals for breaking down large objectives
- **Task**: Individual actionable items with point values
- **TaskCompletion**: Tracks when tasks are completed
### Relationships
- Goals → Milestones (1:many)
- Goals → Tasks (1:many)
- Milestones → Tasks (1:many)
- Tasks → TaskCompletion (1:1)
- Users → Goals, Tasks, UserPoints (1:many)

## 🛠 Technical Stack

- **Backend**: Laravel 12
- **Admin Panel**: Filament PHP 3.3
- **Database**: SQLite (configurable)
- **Frontend**: Livewire (via Filament)
- **Styling**: Tailwind CSS (via Filament)

## 📊 Point System

Points are automatically awarded for:
- **Task Completion**: Variable points based on task complexity
- **Streaks**: Bonus points for consecutive completions
- **Bonuses**: Manual bonus points for achievements
- **Penalties**: Deductions for missed deadlines (configurable)

## 🎮 Gamification Elements

- **Difficulty Levels**: Easy, Medium, Hard goals
- **Point Rewards**: Configurable points per task
- **Progress Tracking**: Visual progress bars and percentages
- **Achievement System**: UserPoints track total and weekly points
- **Weekly Analytics**: Track weekly task completion and points

## 📈 Sample Data

The app includes sample data featuring:
- YouTube Channel Launch goal with milestones and tasks
- Laravel Learning goal with technical tasks
- Point transactions showing various earning scenarios
- Realistic task scheduling with due dates

## 🔧 Customization

### Customizing Point Values
Modify task point values in the Task model or adjust the UserPoints calculation logic in the `addPoints` method.

### Modifying Dashboard Widgets
Update `app/Filament/Widgets/TaskProgressWidget.php` to customize the dashboard cards.

### Custom Goal Types
Extend the difficulty levels in the Goal model and migration.

## 📝 Future Enhancements

- [ ] Team collaboration features
- [ ] Mobile app with notifications
- [ ] Advanced analytics and reporting
- [ ] Social features and leaderboards
- [ ] Integration with external productivity tools
- [ ] Custom achievement badges
- [ ] Time tracking for tasks
- [ ] Recurring tasks and habits

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📜 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

**Sankalpa** (Sanskrit: संकल्प) means "intention" or "resolve" - helping you turn your intentions into achievements! 🎯
