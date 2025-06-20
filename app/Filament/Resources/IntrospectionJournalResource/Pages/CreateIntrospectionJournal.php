<?php

namespace App\Filament\Resources\IntrospectionJournalResource\Pages;

use App\Filament\Resources\IntrospectionJournalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIntrospectionJournal extends CreateRecord
{
    protected static string $resource = IntrospectionJournalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function mount(): void
    {
        parent::mount();
        
        // Pre-fill form based on URL parameters
        if (request()->has('type')) {
            $type = request()->get('type');
            if (in_array($type, ['data_drop', 'learning', 'rule', 'purpose'])) {
                $this->form->fill(['type' => $type]);
            }
        }
    }
}
