<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\WeeklyReviewWidget;
use Filament\Pages\Page;

class WeeklyReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.weekly-report';

    protected static ?string $title = 'Weekly Report';

    protected static ?string $navigationLabel = 'Weekly Report';

    protected static ?string $navigationGroup = 'Planning';

    protected static ?int $navigationSort = 2;

    protected function getHeaderWidgets(): array
    {
        return [
            WeeklyReviewWidget::class,
        ];
    }
} 