<?php

namespace App\Filament\Resources\IntrospectionJournalResource\Widgets;

use App\Models\IntrospectionJournal;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class JournalInsightsWidget extends ChartWidget
{
    protected static ?string $heading = 'Journal Insights - Last 30 Days';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'mood_trends';

    protected function getData(): array
    {
        return match ($this->filter) {
            'mood_trends' => $this->getMoodTrendsData(),
            'entry_types' => $this->getEntryTypesData(),
            'intensity_levels' => $this->getIntensityLevelsData(),
            default => $this->getMoodTrendsData(),
        };
    }

    protected function getType(): string
    {
        return match ($this->filter) {
            'mood_trends' => 'line',
            'entry_types' => 'doughnut',
            'intensity_levels' => 'bar',
            default => 'line',
        };
    }

    protected function getFilters(): ?array
    {
        return [
            'mood_trends' => 'Mood Trends',
            'entry_types' => 'Entry Types',
            'intensity_levels' => 'Intensity Levels',
        ];
    }

    private function getMoodTrendsData(): array
    {
        $last30Days = collect(range(0, 29))->map(function ($i) {
            return Carbon::now()->subDays($i)->format('M j');
        })->reverse()->values();

        $moodCounts = collect(range(0, 29))->map(function ($i) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            return IntrospectionJournal::where('user_id', auth()->id())
                ->whereDate('entry_date', $date)
                ->whereNotNull('mood')
                ->get()
                ->groupBy('mood')
                ->map->count();
        })->reverse()->values();

        $datasets = [];
        $moods = ['very_good', 'good', 'neutral', 'low', 'very_low'];
        $colors = [
            'very_good' => 'rgb(34, 197, 94)',
            'good' => 'rgb(132, 204, 22)',
            'neutral' => 'rgb(107, 114, 128)',
            'low' => 'rgb(251, 146, 60)',
            'very_low' => 'rgb(239, 68, 68)',
        ];

        foreach ($moods as $mood) {
            $data = $moodCounts->map(fn ($dayCounts) => $dayCounts->get($mood, 0));
            
            $datasets[] = [
                'label' => ucwords(str_replace('_', ' ', $mood)),
                'data' => $data->toArray(),
                'borderColor' => $colors[$mood],
                'backgroundColor' => $colors[$mood] . '20',
                'tension' => 0.3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $last30Days->toArray(),
        ];
    }

    private function getEntryTypesData(): array
    {
        $entryCounts = IntrospectionJournal::where('user_id', auth()->id())
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        $typeLabels = [
            'data_drop' => 'Data Drops',
            'learning' => 'Learnings',
            'rule' => 'Rules',
            'purpose' => 'Purpose',
        ];

        $colors = [
            'rgb(59, 130, 246)',
            'rgb(34, 197, 94)',
            'rgb(251, 146, 60)',
            'rgb(168, 85, 247)',
        ];

        $labels = [];
        $data = [];
        $backgroundColors = [];
        $index = 0;

        foreach ($typeLabels as $type => $label) {
            if ($entryCounts->has($type)) {
                $labels[] = $label;
                $data[] = $entryCounts[$type];
                $backgroundColors[] = $colors[$index % count($colors)];
                $index++;
            }
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getIntensityLevelsData(): array
    {
        $intensityCounts = IntrospectionJournal::where('user_id', auth()->id())
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->whereNotNull('intensity_level')
            ->selectRaw('intensity_level, COUNT(*) as count')
            ->groupBy('intensity_level')
            ->orderBy('intensity_level')
            ->pluck('count', 'intensity_level');

        $labels = $intensityCounts->keys()->map(fn ($level) => "Level $level")->toArray();
        $data = $intensityCounts->values()->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Entries',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',   // 1-3: green (low intensity)
                        'rgb(34, 197, 94)',
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',  // 4-6: blue (medium intensity)
                        'rgb(59, 130, 246)',
                        'rgb(59, 130, 246)',
                        'rgb(251, 146, 60)',  // 7-8: orange (high intensity)
                        'rgb(251, 146, 60)',
                        'rgb(239, 68, 68)',   // 9-10: red (very high intensity)
                        'rgb(239, 68, 68)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => $this->filter === 'intensity_levels' ? [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ] : [],
        ];
    }
}
