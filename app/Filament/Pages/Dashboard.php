<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\TaskDashboardWidget;
use App\Filament\Widgets\TaskProgressWidget;
use App\Filament\Widgets\WeeklyPlanningWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            TaskDashboardWidget::class,
            TaskProgressWidget::class,
            WeeklyPlanningWidget::class,
        ];
    }
} 