<?php
// app/Filament/Resources/SitemapResource/Pages/CreateSitemap.php

namespace App\Filament\Resources\SitemapResource\Pages;

use App\Filament\Resources\SitemapResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSitemap extends CreateRecord
{
    protected static string $resource = SitemapResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Auto-generate sitemap after creation
        $this->record->generateSitemap();
    }
}
