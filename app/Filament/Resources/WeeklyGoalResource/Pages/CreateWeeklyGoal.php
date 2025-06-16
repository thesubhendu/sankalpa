<?php

namespace App\Filament\Resources\WeeklyGoalResource\Pages;

use App\Filament\Resources\WeeklyGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CreateWeeklyGoal extends CreateRecord
{
    protected static string $resource = WeeklyGoalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        
        // If using current week toggle, ensure dates are set
        if (isset($data['use_current_week']) && $data['use_current_week']) {
            $data['week_start_date'] = \Carbon\Carbon::now()->startOfWeek()->format('Y-m-d');
            $data['week_end_date'] = \Carbon\Carbon::now()->endOfWeek()->format('Y-m-d');
        }
        
        // Remove the toggle field as it's just a UI helper
        unset($data['use_current_week']);
        
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::create($data);
        
        // Parse the input to create tasks
        if (!empty($data['raw_input'])) {
            $record->parseInput($data['raw_input']);
        }
        
        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
