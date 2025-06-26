<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Posts';

    protected static ?string $modelLabel = 'Post';

    protected static ?string $pluralModelLabel = 'Posts';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Post Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),

                        Forms\Components\TextInput::make('title')
                            ->maxLength(255)
                            ->required(),

                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->helperText('Slug is automatically generated'),

                        Forms\Components\Select::make('type')
                            ->options([
                                'question' => 'Question',
                                'discussion' => 'Discussion',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(60)
                            ->disabled(),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->maxLength(160)
                            ->disabled()
                            ->rows(2),
                    ])->columns(2),

                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Section::make('SEO Settings')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(60)
                            ->helperText('Recommended: 50-60 characters')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('fillFromTitle')
                                    ->icon('heroicon-m-arrow-path')
                                    ->action(function (Forms\Set $set, Forms\Get $get) {
                                        $set('meta_title', $get('title'));
                                    })
                            ),

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
                            ->helperText('Recommended: 1200x630 pixels for social media sharing'),
                    ])->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Post'),

                        Forms\Components\Select::make('featured_type')
                            ->options([
                                'community' => 'Community Featured',
                                'trending' => 'Trending',
                                'editor_choice' => 'Editor\'s Choice',
                            ])
                            ->visible(fn(Forms\Get $get) => $get('is_featured')),

                        Forms\Components\TextInput::make('view_count')
                            ->numeric(),
                    ])->columns(2),

                Forms\Components\Section::make('Statistics')
                    ->schema([
                        Forms\Components\Placeholder::make('vote_score')
                            ->label('Vote Score')
                            ->content(fn(?Post $record) => $record ? $record->vote_score : 0),

                        Forms\Components\Placeholder::make('answers_count')
                            ->label('Answers Count')
                            ->content(fn(?Post $record) => $record ? $record->answers()->count() : 0),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn(?Post $record) => $record ? $record->created_at->format('M d, Y H:i') : ''),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn(?Post $record) => $record ? $record->updated_at->format('M d, Y H:i') : ''),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ImageColumn::make('user.profile_picture')
                    ->label('Author')
                    ->circular()
                    ->size(32)
                    ->defaultImageUrl(function ($record) {
                        return $record->user->getProfileImageUrl();
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->title;
                    })
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'question' => 'primary',
                        'discussion' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('featured_type')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(
                        fn(?string $state): string =>
                        $state ? str_replace('_', ' ', ucwords($state, '_')) : ''
                    )
                    ->visible(fn($record) => $record && $record->is_featured),

                Tables\Columns\TextColumn::make('vote_score')
                    ->label('Score')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state > 10 => 'success',
                        $state > 0 => 'info',
                        $state === 0 => 'gray',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('answers_count')
                    ->counts('answers')
                    ->label('Answers')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(function ($state) {
                        if ($state >= 1000) {
                            return round($state / 1000, 1) . 'k';
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->label('Created')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->label('Updated')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'question' => 'Question',
                        'discussion' => 'Discussion',
                    ]),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('featured')
                    ->query(fn(Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Featured Posts'),

                Tables\Filters\SelectFilter::make('featured_type')
                    ->options([
                        'community' => 'Community Featured',
                        'trending' => 'Trending',
                        'editor_choice' => 'Editor\'s Choice',
                    ]),

                Tables\Filters\Filter::make('popular')
                    ->query(fn(Builder $query): Builder => $query->where('view_count', '>', 100))
                    ->label('Popular (100+ views)'),

                Tables\Filters\Filter::make('unanswered')
                    ->query(fn(Builder $query): Builder => $query->doesntHave('answers'))
                    ->label('Unanswered'),

                Tables\Filters\Filter::make('recent')
                    ->query(fn(Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7)))
                    ->label('Recent (7 days)'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn() => 'posts-' . date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                            ->except([
                                'user.profile_picture', // Remove image columns from export
                                'vote_score', // Computed attributes that cause issues
                                'answers_count', // Count columns that might not export properly
                                'featured_type', // Conditional columns
                            ]),
                    ])
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_frontend')
                    ->label('View Post')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Post $record): string => route('posts.show', $record->slug))
                    ->openUrlInNewTab()
                    ->color('info'),

                Tables\Actions\ViewAction::make()
                    ->label('View Details'),
                Tables\Actions\EditAction::make()
                    ->label('Edit Post'),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Post')
                    ->modalDescription('Are you sure you want to delete this post? This action cannot be undone and will also delete all associated answers, votes, and comments.')
                    ->modalSubmitActionLabel('Yes, delete it')
                    ->successNotificationTitle('Post deleted successfully'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn() => 'selected-posts-' . date('Y-m-d'))
                                ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                                ->except([
                                    'user.profile_picture',
                                    'vote_score',
                                    'answers_count',
                                    'featured_type',
                                ]),
                        ]),

                    Tables\Actions\BulkAction::make('feature')
                        ->label('Mark as Featured')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Feature Selected Posts')
                        ->modalDescription('Are you sure you want to mark these posts as featured?')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_featured' => true]);
                            });
                        })
                        ->successNotificationTitle('Posts marked as featured'),

                    Tables\Actions\BulkAction::make('unfeature')
                        ->label('Remove Featured')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Remove Featured Status')
                        ->modalDescription('Are you sure you want to remove featured status from these posts?')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_featured' => false, 'featured_type' => null]);
                            });
                        })
                        ->successNotificationTitle('Featured status removed'),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Posts')
                        ->modalDescription('Are you sure you want to delete these posts? This action cannot be undone and will also delete all associated data.')
                        ->modalSubmitActionLabel('Yes, delete them')
                        ->successNotificationTitle('Posts deleted successfully'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(null); // Disable default record click navigation
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'answers', 'votes']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
