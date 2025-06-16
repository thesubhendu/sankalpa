<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6">
            <!-- Current Task -->
            @if($currentTask)
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-blue-900">{{ $currentTask->title }}</h3>
                            <p class="text-sm text-blue-700">Currently in progress</p>
                        </div>
                        <x-filament::button 
                            wire:click="completeTask({{ $currentTask->id }})"
                            color="success"
                            size="sm">
                            Complete Task
                        </x-filament::button>
                    </div>
                </div>
            @else
                <!-- Energy Level Selection -->
                @if(!$currentEnergyLevel)
                    <div class="text-center space-y-4">
                        <h2 class="text-xl font-semibold text-gray-900">What is your energy level?</h2>
                        <div class="grid grid-cols-1 gap-3 max-w-md mx-auto">
                            <x-filament::button 
                                wire:click="setEnergyLevel('low')"
                                color="success"
                                size="lg"
                                class="justify-center py-4">
                                🌱 Low Energy
                            </x-filament::button>
                            <x-filament::button 
                                wire:click="setEnergyLevel('medium')"
                                color="warning"
                                size="lg"
                                class="justify-center py-4">
                                ⚡ Medium Energy
                            </x-filament::button>
                            <x-filament::button 
                                wire:click="setEnergyLevel('high')"
                                color="danger"
                                size="lg"
                                class="justify-center py-4">
                                🔥 High Energy
                            </x-filament::button>
                        </div>
                    </div>
                @else
                    <!-- Recommended Task -->
                    @if($recommendedTask)
                        <div class="text-center space-y-4">
                            <h2 class="text-lg font-semibold text-gray-900">Recommended Task</h2>
                            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 rounded-lg p-6 border border-orange-200">
                                <h3 class="text-3xl font-bold text-orange-600 mb-4 bg-orange-100 px-4 py-2 rounded-lg inline-block">{{ $recommendedTask->title }}</h3>
                                @if($recommendedTask->due_date)
                                    <div class="text-sm text-gray-500 mb-6">Due {{ \Carbon\Carbon::parse($recommendedTask->due_date)->format('M j') }}</div>
                                @endif
                                <div class="space-y-3">
                                    <x-filament::button 
                                        wire:click="startTask({{ $recommendedTask->id }})"
                                        color="primary"
                                        size="sm"
                                        class="justify-center">
                                        Start This Task
                                    </x-filament::button>
                                    <x-filament::button 
                                        wire:click="setEnergyLevel(null)"
                                        color="info"
                                        variant="outlined"
                                        size="sm"
                                        class=" justify-center">
                                        Change Energy Level
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center space-y-4">
                            <div class="text-gray-500">
                                <div class="text-6xl mb-2">✅</div>
                                <h3 class="text-lg font-medium">No tasks available</h3>
                                <p class="text-sm">All caught up for your current energy level!</p>
                            </div>
                            <x-filament::button 
                                wire:click="setEnergyLevel(null)"
                                color="gray"
                                variant="outlined"
                                size="sm">
                                Change Energy Level
                            </x-filament::button>
                        </div>
                    @endif
                @endif
            @endif
        </div>

        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('energyLevelUpdated', () => {
                    new FilamentNotification()
                        .title('Energy level updated')
                        .success()
                        .send();
                });

                Livewire.on('taskStarted', () => {
                    new FilamentNotification()
                        .title('Task started!')
                        .success()
                        .send();
                });

                Livewire.on('taskCompleted', () => {
                    new FilamentNotification()
                        .title('Task completed! 🎉')
                        .success()
                        .send();
                });
            });
        </script>
    </x-filament::section>
</x-filament-widgets::widget> 