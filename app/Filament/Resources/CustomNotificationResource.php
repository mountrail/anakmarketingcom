<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomNotificationResource\Pages;
use App\Models\CustomNotification;
use App\Models\Post;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomNotificationResource extends Resource
{
    protected static ?string $model = CustomNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Custom Notifications';

    protected static ?string $modelLabel = 'Custom Notification';

    protected static ?string $pluralModelLabel = 'Custom Notifications';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Notification Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Notification Title'),

                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->label('Notification Message'),

                        Forms\Components\Toggle::make('is_pinned')
                            ->label('Pin Notification')
                            ->helperText('Pinned notifications appear at the top'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active notifications will be shown'),

                        Forms\Components\Toggle::make('use_creator_avatar')
                            ->label('Use Creator Avatar')
                            ->default(false)
                            ->helperText('Use your profile picture instead of system avatar'),
                    ])->columns(2),

                Forms\Components\Section::make('Action Link')
                    ->schema([
                        Forms\Components\Select::make('link_type')
                            ->label('Link Type')
                            ->options([
                                'none' => 'No Link',
                                'post' => 'Post Page',
                                'profile' => 'User Profile',
                                'custom' => 'Custom URL',
                            ])
                            ->default('none')
                            ->reactive()
                            ->required(),

                        Forms\Components\Select::make('link_value')
                            ->label('Select Post')
                            ->options(function () {
                                return Post::latest()
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(function ($post) {
                                        return [
                                            $post->id => "{$post->title} (ID: {$post->id})"
                                        ];
                                    });
                            })
                            ->searchable()
                            ->visible(fn($get) => $get('link_type') === 'post'),

                        Forms\Components\Select::make('link_value')
                            ->label('Select User')
                            ->options(function () {
                                return User::latest()
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(function ($user) {
                                        return [
                                            $user->id => "{$user->name} ({$user->email})"
                                        ];
                                    });
                            })
                            ->searchable()
                            ->visible(fn($get) => $get('link_type') === 'profile'),

                        Forms\Components\TextInput::make('custom_url')
                            ->label('Custom URL')
                            ->url()
                            ->placeholder('https://example.com or /relative-path')
                            ->visible(fn($get) => $get('link_type') === 'custom'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('message')
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->message;
                    }),

                Tables\Columns\BadgeColumn::make('link_type')
                    ->label('Link Type')
                    ->colors([
                        'gray' => 'none',
                        'primary' => 'post',
                        'success' => 'profile',
                        'warning' => 'custom',
                    ]),

                Tables\Columns\IconColumn::make('is_pinned')
                    ->boolean()
                    ->label('Pinned')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->label('Created'),

                Tables\Columns\TextColumn::make('recipients_count')
                    ->label('Recipients')
                    ->getStateUsing(function ($record) {
                        return User::count(); // Shows total users who will receive it
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('link_type')
                    ->options([
                        'none' => 'No Link',
                        'post' => 'Post Page',
                        'profile' => 'User Profile',
                        'custom' => 'Custom URL',
                    ]),

                Tables\Filters\Filter::make('is_pinned')
                    ->query(fn(Builder $query): Builder => $query->where('is_pinned', true))
                    ->label('Pinned Only'),

                Tables\Filters\Filter::make('is_active')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomNotifications::route('/'),
            'create' => Pages\CreateCustomNotification::route('/create'),
            'edit' => Pages\EditCustomNotification::route('/{record}/edit'),
        ];
    }
}
