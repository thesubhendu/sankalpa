<?php

namespace App\Filament\Resources\UserPointsResource\Pages;

use App\Filament\Resources\UserPointsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserPoints extends ListRecords
{
    protected static string $resource = UserPointsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
