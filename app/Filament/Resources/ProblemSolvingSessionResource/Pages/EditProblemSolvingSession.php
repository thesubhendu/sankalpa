<?php

namespace App\Filament\Resources\ProblemSolvingSessionResource\Pages;

use App\Filament\Resources\ProblemSolvingSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProblemSolvingSession extends EditRecord
{
    protected static string $resource = ProblemSolvingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
