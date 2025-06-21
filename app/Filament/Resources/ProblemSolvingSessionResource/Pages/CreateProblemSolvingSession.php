<?php

namespace App\Filament\Resources\ProblemSolvingSessionResource\Pages;

use App\Filament\Resources\ProblemSolvingSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProblemSolvingSession extends CreateRecord
{
    protected static string $resource = ProblemSolvingSessionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        
        return $data;
    }
}
