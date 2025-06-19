<?php

namespace App\Filament\Resources\CustomNotificationResource\Pages;

use App\Filament\Resources\CustomNotificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomNotification extends CreateRecord
{
    protected static string $resource = CustomNotificationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
