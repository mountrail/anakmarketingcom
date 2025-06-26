<?php
// app/Filament/Resources/SitemapResource/Pages/ListSitemaps.php

namespace App\Filament\Resources\SitemapResource\Pages;

use App\Filament\Resources\SitemapResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSitemaps extends ListRecords
{
    protected static string $resource = SitemapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('regenerate_all')
                ->label('Regenerate All')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $sitemaps = \App\Models\Sitemap::where('is_active', true)->get();
                    foreach ($sitemaps as $sitemap) {
                        $sitemap->generateSitemap();
                    }
                    \Filament\Notifications\Notification::make()
                        ->title('All active sitemaps regenerated successfully!')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->color('success'),
        ];
    }
}
