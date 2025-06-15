<?php

namespace App\Filament\Resources\WeeklyGoalResource\Pages;

use App\Filament\Resources\WeeklyGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWeeklyGoal extends CreateRecord
{
    protected static string $resource = WeeklyGoalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        
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
