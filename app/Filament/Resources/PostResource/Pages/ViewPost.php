<?php
// app\Filament\Resources\PostResource\pages\ViewPost.php
namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;

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

            Actions\EditAction::make()  // Add this action
                ->label('Edit SEO')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->form([
                    Forms\Components\Section::make('SEO Settings')
                        ->schema([
                            Forms\Components\TextInput::make('meta_title')
                                ->label('Meta Title')
                                ->maxLength(60)
                                ->helperText('Recommended: 50-60 characters'),

                            Forms\Components\Textarea::make('meta_description')
                                ->label('Meta Description')
                                ->maxLength(160)
                                ->rows(3)
                                ->helperText('Recommended: 150-160 characters'),

                            Forms\Components\TextInput::make('meta_keywords')
                                ->label('Meta Keywords')
                                ->helperText('Separate keywords with commas'),

                            Forms\Components\FileUpload::make('og_image')
                                ->label('Open Graph Image')
                                ->image()
                                ->disk('public')
                                ->directory('og-images')
                                ->helperText('Recommended: 1200x630 pixels'),
                        ])->columns(2),
                ])
                ->action(function (array $data): void {
                    $this->record->update($data);

                    $this->getSuccessNotification(
                        title: 'SEO settings updated successfully'
                    )?->send();
                }),
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

                    $this->getSuccessNotification(
                        title: $message
                    )?->send();
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
        return [];
    }
}
