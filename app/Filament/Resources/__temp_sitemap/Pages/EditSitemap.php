<?php
// app/Filament/Resources/SitemapResource/Pages/EditSitemap.php

namespace App\Filament\Resources\SitemapResource\Pages;

use App\Filament\Resources\SitemapResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSitemap extends EditRecord
{
    protected static string $resource = SitemapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('regenerate')
                ->label('Regenerate Sitemap')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->record->generateSitemap();
                    \Filament\Notifications\Notification::make()
                        ->title('Sitemap regenerated successfully!')
                        ->success()
                        ->send();
                })
                ->color('success'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
