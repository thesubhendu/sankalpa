<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Demo Banner --}}
        @if($isDemo)
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <div class="flex items-center">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mr-2"/>
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        <strong>Demo Mode:</strong> You don't have any journal entries yet. Showing sample data to demonstrate the Daily Review feature. 
                        <a href="{{ route('filament.admin.resources.introspection-journals.create') }}" class="underline font-medium">Create your first entry</a> to see your personal content here.
                    </p>
                </div>
            </div>
        @endif
        {{-- Must Read Rules Section --}}
        @if($mustReadRules->count() > 0)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-red-800 dark:text-red-200 mb-4 flex items-center">
                    <x-heroicon-o-shield-exclamation class="w-6 h-6 mr-2"/>
                    MUST READ EVERYDAY (Most Important Rules)
                </h2>
                <div class="space-y-4">
                    @foreach($mustReadRules as $rule)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-red-100 dark:border-red-800">
                            <h3 class="font-semibold text-lg text-red-700 dark:text-red-300 mb-2">{{ $rule->title }}</h3>
                            <div class="prose dark:prose-invert max-w-none">
                                {!! \Illuminate\Support\Str::markdown($rule->content) !!}
                            </div>
                            @if($rule->tags)
                                <div class="flex flex-wrap gap-1 mt-3">
                                    @foreach($rule->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Key Learnings Section --}}
        @if($keyLearnings->count() > 0)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-yellow-800 dark:text-yellow-200 mb-4 flex items-center">
                    <x-heroicon-o-light-bulb class="w-6 h-6 mr-2"/>
                    Learnings About Me (Things That Expose You)
                </h2>
                <div class="space-y-4">
                    @foreach($keyLearnings as $learning)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-yellow-100 dark:border-yellow-800">
                            <h3 class="font-semibold text-lg text-yellow-700 dark:text-yellow-300 mb-2">{{ $learning->title }}</h3>
                            <div class="prose dark:prose-invert max-w-none">
                                {!! \Illuminate\Support\Str::markdown($learning->content) !!}
                            </div>
                            @if($learning->tags)
                                <div class="flex flex-wrap gap-1 mt-3">
                                    @foreach($learning->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- My Purpose Section --}}
        @if($purposes->count() > 0)
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-blue-800 dark:text-blue-200 mb-4 flex items-center">
                    <x-heroicon-o-flag class="w-6 h-6 mr-2"/>
                    My Purpose
                </h2>
                <div class="space-y-4">
                    @foreach($purposes as $purpose)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-blue-100 dark:border-blue-800">
                            <h3 class="font-semibold text-lg text-blue-700 dark:text-blue-300 mb-2">{{ $purpose->title }}</h3>
                            <div class="prose dark:prose-invert max-w-none">
                                {!! \Illuminate\Support\Str::markdown($purpose->content) !!}
                            </div>
                            @if($purpose->tags)
                                <div class="flex flex-wrap gap-1 mt-3">
                                    @foreach($purpose->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Pending Reviews Section --}}
        @if($pendingReviews->count() > 0)
            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-purple-800 dark:text-purple-200 mb-4 flex items-center">
                    <x-heroicon-o-clock class="w-6 h-6 mr-2"/>
                    Pending Reviews ({{ $pendingReviews->count() }})
                </h2>
                <div class="space-y-3">
                    @foreach($pendingReviews as $review)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-purple-100 dark:border-purple-800">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-purple-700 dark:text-purple-300">{{ $review->title }}</h3>
                                    <p class="text-sm text-purple-600 dark:text-purple-400 mb-2">
                                        {{ $review->type_display }} • {{ $review->entry_date->format('M j, Y') }}
                                        @if($review->mood)
                                            • Mood: {{ $review->mood_display }}
                                        @endif
                                    </p>
                                    @if($review->trigger)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            <strong>Trigger:</strong> {{ $review->trigger }}
                                        </p>
                                    @endif
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($review->content), 150) }}
                                    </p>
                                </div>
                                <a href="{{ route('filament.admin.resources.introspection-journals.view', $review) }}" 
                                   class="ml-4 inline-flex items-center px-3 py-1 border border-purple-300 rounded-md text-xs font-medium text-purple-700 bg-purple-100 hover:bg-purple-200 dark:bg-purple-900/50 dark:text-purple-200 dark:border-purple-600">
                                    Review
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Recent Important Data Drops --}}
        @if($recentDataDrops->count() > 0)
            <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 mr-2"/>
                    Recent Important Data Drops
                </h2>
                <div class="space-y-3">
                    @foreach($recentDataDrops as $drop)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-100 dark:border-gray-700">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-700 dark:text-gray-300">{{ $drop->title }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        {{ $drop->entry_date->format('M j, Y') }}
                                        @if($drop->mood)
                                            • Mood: {{ $drop->mood_display }}
                                        @endif
                                        @if($drop->intensity_level)
                                            • Intensity: {{ $drop->intensity_level }}/10
                                        @endif
                                    </p>
                                    @if($drop->trigger)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            <strong>Trigger:</strong> {{ $drop->trigger }}
                                        </p>
                                    @endif
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($drop->content), 100) }}
                                    </p>
                                </div>
                                <a href="{{ route('filament.admin.resources.introspection-journals.view', $drop) }}" 
                                   class="ml-4 inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                                    View
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Quick Actions --}}
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-6">
            <h2 class="text-xl font-bold text-green-800 dark:text-green-200 mb-4 flex items-center">
                <x-heroicon-o-plus-circle class="w-5 h-5 mr-2"/>
                Quick Actions
            </h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('filament.admin.resources.introspection-journals.create') }}?type=data_drop" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Data Drop
                </a>
                <a href="{{ route('filament.admin.resources.introspection-journals.create') }}?type=learning" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    New Learning
                </a>
                <a href="{{ route('filament.admin.resources.introspection-journals.create') }}?type=rule" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    New Rule
                </a>
                <a href="{{ route('filament.admin.resources.introspection-journals.create') }}?type=purpose" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                    </svg>
                    New Purpose
                </a>
                <a href="{{ route('filament.admin.resources.introspection-journals.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    View All Entries
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page> 