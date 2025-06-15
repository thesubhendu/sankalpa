<?php

namespace App\Filament\Resources\WeeklyGoalResource\Pages;

use App\Filament\Resources\WeeklyGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditWeeklyGoal extends EditRecord
{
    protected static string $resource = WeeklyGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $originalRawInput = $record->raw_input;
        
        $record->update($data);
        
        // If raw_input changed, re-parse the tasks
        if ($originalRawInput !== $data['raw_input']) {
            // Delete existing tasks
            $record->tasks()->delete();
            
            // Re-parse the new input
            if (!empty($data['raw_input'])) {
                $record->parseInput($data['raw_input']);
            }
        }
        
        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
