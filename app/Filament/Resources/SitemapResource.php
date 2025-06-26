<?php
// app/Filament/Resources/SitemapResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\SitemapResource\Pages;
use App\Models\Sitemap;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SitemapResource extends Resource
{
    protected static ?string $model = Sitemap::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Sitemaps';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sitemap Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Sitemap Name'),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'posts' => 'Posts',
                                'users' => 'User Profiles',
                                'static' => 'Static Pages',
                                'custom' => 'Custom URLs',
                            ])
                            ->reactive(),

                        Forms\Components\TextInput::make('filename')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('sitemap-posts.xml')
                            ->helperText('File will be accessible at /sitemaps/{filename}'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Forms\Components\TextInput::make('priority')
                            ->numeric()
                            ->default(0.8)
                            ->step(0.1)
                            ->minValue(0.1)
                            ->maxValue(1.0)
                            ->helperText('Priority for sitemap index (0.1-1.0)'),

                        Forms\Components\Select::make('changefreq')
                            ->options([
                                'always' => 'Always',
                                'hourly' => 'Hourly',
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                                'yearly' => 'Yearly',
                                'never' => 'Never',
                            ])
                            ->default('weekly'),
                    ])->columns(2),

                Forms\Components\Section::make('Custom URLs')
                    ->schema([
                        Forms\Components\Repeater::make('custom_urls')
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->required()
                                    ->url()
                                    ->placeholder('https://example.com/page'),
                                Forms\Components\Select::make('priority')
                                    ->options([
                                        '1.0' => 'Very High (1.0)',
                                        '0.8' => 'High (0.8)',
                                        '0.6' => 'Medium (0.6)',
                                        '0.4' => 'Low (0.4)',
                                        '0.2' => 'Very Low (0.2)',
                                    ])
                                    ->default('0.6'),
                                Forms\Components\Select::make('changefreq')
                                    ->options([
                                        'daily' => 'Daily',
                                        'weekly' => 'Weekly',
                                        'monthly' => 'Monthly',
                                        'yearly' => 'Yearly',
                                    ])
                                    ->default('monthly'),
                            ])
                            ->columns(3)
                            ->visible(fn($get) => $get('type') === 'custom'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'posts',
                        'success' => 'users',
                        'warning' => 'static',
                        'danger' => 'custom',
                    ]),

                Tables\Columns\TextColumn::make('filename')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('priority')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('changefreq')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\Filter::make('is_active')
                    ->query(fn($query) => $query->where('is_active', true)),
            ])
            ->actions([
                Tables\Actions\Action::make('generate')
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn($record) => $record->generateSitemap())
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSitemaps::route('/'),
            'create' => Pages\CreateSitemap::route('/create'),
            'edit' => Pages\EditSitemap::route('/{record}/edit'),
        ];
    }
}
