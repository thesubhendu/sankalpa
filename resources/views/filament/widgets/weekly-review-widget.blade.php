<div class="fi-wi-weekly-review">
    <div class="grid gap-6">
        <!-- Monthly Overview -->
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ now()->format('F Y') }} Overview
                </h2>
                <div class="flex items-center space-x-2">
                    @php
                        $trendColor = match($trends['trend_direction']) {
                            'improving' => 'text-green-600',
                            'declining' => 'text-red-600',
                            default => 'text-gray-600'
                        };
                        $trendIcon = match($trends['trend_direction']) {
                            'improving' => '📈',
                            'declining' => '📉',
                            default => '➡️'
                        };
                    @endphp
                    <span class="{{ $trendColor }}">
                        {{ $trendIcon }} {{ ucfirst($trends['trend_direction']) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ $monthlyStats['total_goals'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Weekly Goals</div>
                </div>
                <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ $monthlyStats['completion_rate'] }}%</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</div>
                </div>
                <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ $monthlyStats['completed_tasks'] }}/{{ $monthlyStats['total_tasks'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Tasks Done</div>
                </div>
                <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-orange-600">{{ $monthlyStats['earned_points'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Points Earned</div>
                </div>
            </div>
        </div>

        <!-- Trend Chart -->
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">8-Week Trend</h3>
            <div class="flex items-end space-x-2 h-32">
                @foreach($trends['weekly_data'] as $week)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-t {{ $week['is_current'] ? 'bg-blue-200 dark:bg-blue-800' : '' }}" 
                             style="height: {{ $week['completion_rate'] }}%">
                            <div class="w-full bg-green-500 rounded-t" style="height: {{ $week['completion_rate'] }}%"></div>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1 transform -rotate-45 origin-bottom-left">
                            {{ $week['week_label'] }}
                        </div>
                        <div class="text-xs font-medium text-gray-900 dark:text-white">
                            {{ $week['completion_rate'] }}%
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                Average completion rate: {{ round($trends['avg_completion_rate']) }}% • Average points per week: {{ round($trends['avg_points_per_week']) }}
            </div>
        </div>

        <!-- Past Weeks Performance -->
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Weekly Performance</h3>
            @if($pastWeeks->count() > 0)
                <div class="space-y-4">
                    @foreach($pastWeeks as $week)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $week['goal']->title }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $week['week_label'] }} • {{ $week['days_ago'] }} days ago
                                    </p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <div class="text-sm font-medium">{{ $week['completion_rate'] }}% complete</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ $week['completed_tasks'] }}/{{ $week['total_tasks'] }} tasks
                                        </div>
                                    </div>
                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $week['completion_rate'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center space-x-4">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        Points: {{ $week['earned_points'] }}/{{ $week['total_points'] }}
                                    </span>
                                    @if($week['goal']->goal)
                                        <span class="text-blue-600 dark:text-blue-400">
                                            🎯 {{ $week['goal']->goal->title }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('filament.admin.resources.weekly-goals.view', $week['goal']) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-xs">View Details</a>
                                </div>
                            </div>

                            <!-- Performance Indicators -->
                            <div class="mt-3 flex space-x-2">
                                @if($week['completion_rate'] >= 80)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Excellent</span>
                                @elseif($week['completion_rate'] >= 60)
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Good</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Needs Improvement</span>
                                @endif

                                @if($week['point_efficiency'] >= 80)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">High Impact</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-2">📊</div>
                    <p class="text-gray-600 dark:text-gray-400">No completed weeks yet.</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">Start creating weekly goals to see your progress here!</p>
                </div>
            @endif
        </div>

        <!-- Insights & Recommendations -->
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Insights & Recommendations</h3>
            <div class="space-y-3">
                @if($trends['trend_direction'] === 'improving')
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-green-600 mr-2">🎉</span>
                            <span class="text-green-800 dark:text-green-200 font-medium">Great progress!</span>
                        </div>
                        <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                            Your completion rate has been improving over the past few weeks. Keep up the great work!
                        </p>
                    </div>
                @elseif($trends['trend_direction'] === 'declining')
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-red-600 mr-2">⚠️</span>
                            <span class="text-red-800 dark:text-red-200 font-medium">Room for improvement</span>
                        </div>
                        <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                            Your completion rate has declined recently. Consider reducing task load or breaking them into smaller pieces.
                        </p>
                    </div>
                @else
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-blue-600 mr-2">📈</span>
                            <span class="text-blue-800 dark:text-blue-200 font-medium">Steady progress</span>
                        </div>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            You're maintaining a consistent pace. Consider challenging yourself with slightly more ambitious goals.
                        </p>
                    </div>
                @endif

                @if($monthlyStats['completion_rate'] < 50)
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-yellow-600 mr-2">💡</span>
                            <span class="text-yellow-800 dark:text-yellow-200 font-medium">Tip: Start smaller</span>
                        </div>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                            Try creating fewer, more focused weekly goals. Quality over quantity often leads to better results.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div> 