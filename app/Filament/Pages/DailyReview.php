<?php

namespace App\Filament\Pages;

use App\Models\IntrospectionJournal;
use Filament\Pages\Page;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class DailyReview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.daily-review';

    protected static ?string $navigationLabel = 'Daily Review';

    protected static ?string $title = 'Daily Review';

    protected static ?string $navigationGroup = 'Self Development';

    protected static ?int $navigationSort = 0;

    public function mount(): void
    {
        // You can add any initialization logic here
    }

    protected function getViewData(): array
    {
        $userId = auth()->id();

        // If no entries exist for current user, show sample data from any user for demo purposes
        $hasUserEntries = IntrospectionJournal::where('user_id', $userId)->exists();
        $queryUserId = $hasUserEntries ? $userId : null;

        $baseQuery = IntrospectionJournal::query();
        if ($queryUserId) {
            $baseQuery->where('user_id', $queryUserId);
        }

        return [
            'mustReadRules' => (clone $baseQuery)
                ->where('type', 'rule')
                ->where('is_important', true)
                ->orderBy('created_at', 'desc')
                ->get(),

            'keyLearnings' => (clone $baseQuery)
                ->where('type', 'learning')
                ->where('is_important', true)
                ->orderBy('created_at', 'desc')
                ->get(),

            'purposes' => (clone $baseQuery)
                ->where('type', 'purpose')
                ->orderBy('created_at', 'desc')
                ->get(),

            'pendingReviews' => (clone $baseQuery)
                ->where('needs_review', true)
                ->orderBy('entry_date', 'desc')
                ->limit(5)
                ->get(),

            'recentDataDrops' => (clone $baseQuery)
                ->where('type', 'data_drop')
                ->where('is_important', true)
                ->orderBy('entry_date', 'desc')
                ->limit(3)
                ->get(),

            'isDemo' => !$hasUserEntries,
        ];
    }
} 