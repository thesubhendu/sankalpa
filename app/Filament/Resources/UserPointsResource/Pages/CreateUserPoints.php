<?php

namespace App\Filament\Resources\UserPointsResource\Pages;

use App\Filament\Resources\UserPointsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserPoints extends CreateRecord
{
    protected static string $resource = UserPointsResource::class;
}
