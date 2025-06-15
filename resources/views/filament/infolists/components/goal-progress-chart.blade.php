@php
    $goal = $getRecord();
    $totalTasks = $goal->tasks()->count();
    $completedTasks = $goal->tasks()->where('status', 'done')->count();
    $inProgressTasks = $goal->tasks()->where('status', 'in_progress')->count();
    $pendingTasks = $goal->tasks()->where('status', 'pending')->count();
@endphp

<div class="space-y-6">
    {{-- Overall Progress Bar --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="flex justify-between items-center mb-2">
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Overall Progress</h4>
            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($goal->completion_percentage, 1) }}%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
            <div class="bg-green-500 h-3 rounded-full transition-all duration-300" style="width: {{ $goal->completion_percentage }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mt-2">
            <span>{{ $completedTasks }} of {{ $totalTasks }} tasks completed</span>
            <span>{{ $goal->total_points }} points earned</span>
        </div>
    </div>

    {{-- Task Status Breakdown --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $completedTasks }}</div>
            <div class="text-sm text-green-700 dark:text-green-300">Completed</div>
        </div>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $inProgressTasks }}</div>
            <div class="text-sm text-yellow-700 dark:text-yellow-300">In Progress</div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $pendingTasks }}</div>
            <div class="text-sm text-gray-700 dark:text-gray-300">Pending</div>
        </div>
    </div>
</div> 