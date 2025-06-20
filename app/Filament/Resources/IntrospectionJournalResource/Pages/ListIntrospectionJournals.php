<?php

namespace App\Filament\Resources\IntrospectionJournalResource\Pages;

use App\Filament\Resources\IntrospectionJournalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIntrospectionJournals extends ListRecords
{
    protected static string $resource = IntrospectionJournalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            IntrospectionJournalResource\Widgets\JournalInsightsWidget::class,
        ];
    }
}
