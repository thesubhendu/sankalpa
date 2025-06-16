<div class="space-y-6">
    <!-- Goal Summary -->
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $record->title }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-600 dark:text-gray-400">Week:</span><br>
                <span class="font-medium">{{ $record->week_start_date->format('M j') }} - {{ $record->week_end_date->format('M j, Y') }}</span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Total Tasks:</span><br>
                <span class="font-medium">{{ $record->tasks->count() }}</span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Progress:</span><br>
                <span class="font-medium">{{ $record->completion_percentage }}%</span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Points:</span><br>
                <span class="font-medium">{{ $record->tasks->where('status', 'done')->sum('points') }}/{{ $record->total_points }}</span>
            </div>
        </div>
        
        @if($record->goal)
            <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <span class="text-sm text-blue-600 dark:text-blue-400">
                    🎯 Linked to: {{ $record->goal->title }}
                </span>
            </div>
        @endif
    </div>

    <!-- Tasks by Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Pending Tasks -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">
                ⏳ Pending ({{ $record->tasks->where('status', 'pending')->count() }})
            </h4>
            <div class="space-y-2">
                @forelse($record->tasks->where('status', 'pending') as $task)
                    <div class="text-sm p-2 bg-gray-50 dark:bg-gray-800 rounded">
                        <div class="font-medium">{{ $task->title }}</div>
                        <div class="text-gray-600 dark:text-gray-400">
                            {{ $task->points }} pts • {{ $task->energy_level }} • 
                            {{ $task->due_date ? $task->due_date->format('M j') : 'No due date' }}
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No pending tasks</p>
                @endforelse
            </div>
        </div>

        <!-- In Progress Tasks -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">
                🔄 In Progress ({{ $record->tasks->where('status', 'in_progress')->count() }})
            </h4>
            <div class="space-y-2">
                @forelse($record->tasks->where('status', 'in_progress') as $task)
                    <div class="text-sm p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded">
                        <div class="font-medium">{{ $task->title }}</div>
                        <div class="text-gray-600 dark:text-gray-400">
                            {{ $task->points }} pts • {{ $task->energy_level }} • 
                            Started: {{ $task->started_at ? $task->started_at->format('M j, g:i A') : 'Unknown' }}
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No tasks in progress</p>
                @endforelse
            </div>
        </div>

        <!-- Completed Tasks -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">
                ✅ Completed ({{ $record->tasks->where('status', 'done')->count() }})
            </h4>
            <div class="space-y-2">
                @forelse($record->tasks->where('status', 'done') as $task)
                    <div class="text-sm p-2 bg-green-50 dark:bg-green-900/20 rounded">
                        <div class="font-medium">{{ $task->title }}</div>
                        <div class="text-gray-600 dark:text-gray-400">
                            {{ $task->points }} pts • {{ $task->energy_level }} • 
                            Done: {{ $task->completed_at ? $task->completed_at->format('M j, g:i A') : 'Unknown' }}
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No completed tasks</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Energy & Priority Distribution -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Energy Distribution -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Energy Distribution</h4>
            <div class="space-y-2">
                @php
                    $energyGroups = $record->tasks->groupBy('energy_level');
                @endphp
                <div class="flex justify-between text-sm">
                    <span>🔥 High Energy:</span>
                    <span class="font-medium">{{ $energyGroups->get('high', collect())->count() }} tasks</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>⚡ Medium Energy:</span>
                    <span class="font-medium">{{ $energyGroups->get('medium', collect())->count() }} tasks</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>🌱 Low Energy:</span>
                    <span class="font-medium">{{ $energyGroups->get('low', collect())->count() }} tasks</span>
                </div>
            </div>
        </div>

        <!-- Priority Distribution -->
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Priority Distribution</h4>
            <div class="space-y-2">
                @php
                    $priorityGroups = $record->tasks->groupBy('priority');
                @endphp
                <div class="flex justify-between text-sm">
                    <span>🔴 High Priority:</span>
                    <span class="font-medium">{{ $priorityGroups->get('3', collect())->count() }} tasks</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>🟡 Medium Priority:</span>
                    <span class="font-medium">{{ $priorityGroups->get('2', collect())->count() }} tasks</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>🟢 Low Priority:</span>
                    <span class="font-medium">{{ $priorityGroups->get('1', collect())->count() }} tasks</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Generated Summary -->
    @if($record->parsed_summary)
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Generated Summary</h4>
            <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $record->parsed_summary }}</pre>
        </div>
    @endif
</div> 