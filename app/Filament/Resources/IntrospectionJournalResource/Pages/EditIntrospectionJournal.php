<?php

namespace App\Filament\Resources\IntrospectionJournalResource\Pages;

use App\Filament\Resources\IntrospectionJournalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIntrospectionJournal extends EditRecord
{
    protected static string $resource = IntrospectionJournalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
