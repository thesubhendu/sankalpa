<?php

namespace App\Filament\Resources\ProblemSolvingSessionResource\Pages;

use App\Filament\Resources\ProblemSolvingSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProblemSolvingSessions extends ListRecords
{
    protected static string $resource = ProblemSolvingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
