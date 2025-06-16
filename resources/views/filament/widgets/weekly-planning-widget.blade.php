<div class="fi-wi-weekly-planning">
    <div class="grid gap-6">
        <!-- Header with Current Week Info -->
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Week of {{ $currentWeek['week_start']->format('M j') }} - {{ $currentWeek['week_end']->format('M j, Y') }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ $currentWeek['total_goals'] }} goals • {{ $currentWeek['total_tasks'] }} tasks • {{ $currentWeek['earned_points'] }}/{{ $currentWeek['total_points'] }} points
                    </p>
                </div>
                <div class="flex space-x-2">
                    @if($currentWeek['total_goals'] == 0)
                        <button 
                            wire:click="createQuickWeeklyGoal"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                        >
                            Start Weekly Planning
                        </button>
                    @endif
                    <a href="{{ route('filament.admin.resources.weekly-goals.create') }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Add Weekly Goal
                    </a>
                </div>
            </div>

            <!-- Week Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <span>Weekly Progress</span>
                    <span>{{ $currentWeek['completed_tasks'] }}/{{ $currentWeek['total_tasks'] }} tasks</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $currentWeek['total_tasks'] > 0 ? ($currentWeek['completed_tasks'] / $currentWeek['total_tasks']) * 100 : 0 }}%"></div>
                </div>
            </div>

            <!-- Daily Progress Grid -->
            <div class="grid grid-cols-7 gap-2">
                @foreach($weekProgress as $day)
                    <div class="text-center p-2 rounded-lg {{ $day['is_today'] ? 'bg-blue-100 dark:bg-blue-900' : ($day['is_past'] ? 'bg-gray-50 dark:bg-gray-800' : 'bg-white dark:bg-gray-900') }}">
                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ substr($day['day'], 0, 3) }}</div>
                        <div class="text-lg font-bold {{ $day['is_today'] ? 'text-blue-600' : 'text-gray-900 dark:text-white' }}">
                            {{ $day['date']->format('j') }}
                        </div>
                        @if($day['total_tasks'] > 0)
                            <div class="text-xs {{ $day['completed_tasks'] == $day['total_tasks'] ? 'text-green-600' : 'text-gray-500' }}">
                                {{ $day['completed_tasks'] }}/{{ $day['total_tasks'] }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Weekly Goals Overview -->
        @if($currentWeek['goals']->count() > 0)
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">This Week's Goals</h3>
                <div class="space-y-4">
                    @foreach($currentWeek['goals'] as $goal)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $goal->title }}</h4>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $goal->completion_percentage }}% complete
                                    </span>
                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $goal->completion_percentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $goal->tasks->count() }} tasks • {{ $goal->tasks->where('status', 'done')->sum('points') }}/{{ $goal->total_points }} points
                                @if($goal->goal)
                                    • Linked to: {{ $goal->goal->title }}
                                @endif
                            </div>
                            <div class="flex space-x-2 mt-2">
                                <a href="{{ route('filament.admin.resources.weekly-goals.edit', $goal) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
                                <a href="{{ route('filament.admin.resources.weekly-goals.view', $goal) }}" 
                                   class="text-green-600 hover:text-green-800 text-sm">View Details</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Today's Tasks and Upcoming Tasks -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Today's Tasks -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Today's Tasks</h3>
                @if($todayTasks->count() > 0)
                    <div class="space-y-3">
                        @foreach($todayTasks as $task)
                            <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $task->title }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $task->points }} pts • {{ $task->energy_level }} energy • {{ $task->priority_text }} priority
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $task->status === 'done' ? 'bg-green-100 text-green-800' : ($task->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400">No tasks scheduled for today.</p>
                @endif
            </div>

            <!-- Upcoming Tasks -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Upcoming Tasks</h3>
                @if($upcomingTasks->count() > 0)
                    <div class="space-y-3">
                        @foreach($upcomingTasks as $task)
                            <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $task->title }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Due: {{ $task->due_date->format('M j, Y') }} • {{ $task->points }} pts • {{ $task->priority_text }} priority
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                    {{ $task->due_date->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400">No upcoming tasks.</p>
                @endif
            </div>
        </div>

        <!-- Weekly Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Status Distribution -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Task Status</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Pending</span>
                        <span class="font-medium">{{ $weeklyStats['by_status']['pending'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">In Progress</span>
                        <span class="font-medium">{{ $weeklyStats['by_status']['in_progress'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Done</span>
                        <span class="font-medium text-green-600">{{ $weeklyStats['by_status']['done'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Energy Distribution -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Energy Levels</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">🌱 Low</span>
                        <span class="font-medium">{{ $weeklyStats['by_energy']['low'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">⚡ Medium</span>
                        <span class="font-medium">{{ $weeklyStats['by_energy']['medium'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">🔥 High</span>
                        <span class="font-medium">{{ $weeklyStats['by_energy']['high'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Priority Distribution -->
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Priority Levels</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Low</span>
                        <span class="font-medium">{{ $weeklyStats['by_priority']['low'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Medium</span>
                        <span class="font-medium">{{ $weeklyStats['by_priority']['medium'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">High</span>
                        <span class="font-medium text-red-600">{{ $weeklyStats['by_priority']['high'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 