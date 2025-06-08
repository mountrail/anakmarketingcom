<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_frontend')
                ->label('View on Frontend')
                ->icon('heroicon-o-eye')
                ->url(fn(): string => route('posts.show', $this->record->slug))
                ->openUrlInNewTab()
                ->color('info'),

            Actions\Action::make('toggle_featured')
                ->label(fn() => $this->record->is_featured ? 'Remove Featured' : 'Mark as Featured')
                ->icon(fn() => $this->record->is_featured ? 'heroicon-o-x-mark' : 'heroicon-o-star')
                ->color(fn() => $this->record->is_featured ? 'gray' : 'warning')
                ->requiresConfirmation()
                ->modalHeading(fn() => $this->record->is_featured ? 'Remove Featured Status' : 'Mark as Featured')
                ->modalDescription(fn() => $this->record->is_featured
                    ? 'Are you sure you want to remove the featured status from this post?'
                    : 'Are you sure you want to mark this post as featured?')
                ->action(function () {
                    $this->record->update([
                        'is_featured' => !$this->record->is_featured,
                        'featured_type' => !$this->record->is_featured ? null : $this->record->featured_type,
                    ]);

                    $message = $this->record->is_featured
                        ? 'Post marked as featured successfully'
                        : 'Featured status removed successfully';

                    $this->getSuccessNotification()
                        ->title($message)
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Post')
                ->modalDescription('Are you sure you want to delete this post? This action cannot be undone and will also delete all associated answers, votes, and comments.')
                ->modalSubmitActionLabel('Yes, delete it')
                ->successNotificationTitle('Post deleted successfully')
                ->successRedirectUrl(fn() => PostResource::getUrl('index')),
        ];
    }

    public function getFormActions(): array
    {
        return [
            // Remove default save action since form is disabled
        ];
    }
}
