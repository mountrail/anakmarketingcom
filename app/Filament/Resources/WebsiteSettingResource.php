<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebsiteSettingResource\Pages;
use App\Models\WebsiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsiteSettingResource extends Resource
{
    protected static ?string $model = WebsiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Website Settings';

    protected static ?string $modelLabel = 'Website Setting';

    protected static ?string $pluralModelLabel = 'Website Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setting Details')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Unique identifier for this setting'),

                        Forms\Components\Select::make('group')
                            ->options([
                                'general' => 'General',
                                'seo' => 'SEO',
                                'social' => 'Social Media',
                                'contact' => 'Contact',
                                'appearance' => 'Appearance',
                            ])
                            ->required()
                            ->default('general'),

                        Forms\Components\Select::make('type')
                            ->options([
                                'text' => 'Text',
                                'textarea' => 'Textarea',
                                'boolean' => 'Boolean',
                                'number' => 'Number',
                                'url' => 'URL',
                                'email' => 'Email',
                                'image' => 'Image',
                            ])
                            ->required()
                            ->default('text')
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('value', null)),
                    ])->columns(2),

                Forms\Components\Section::make('Setting Value')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->visible(fn(Forms\Get $get): bool => in_array($get('type'), ['text', 'url', 'email', 'number']))
                            ->type(fn(Forms\Get $get): string => match ($get('type')) {
                                'url' => 'url',
                                'email' => 'email',
                                'number' => 'number',
                                default => 'text'
                            }),

                        Forms\Components\Textarea::make('value')
                            ->label('Value')
                            ->visible(fn(Forms\Get $get): bool => $get('type') === 'textarea')
                            ->rows(4),

                        Forms\Components\Toggle::make('value')
                            ->label('Value')
                            ->visible(fn(Forms\Get $get): bool => $get('type') === 'boolean'),

                        Forms\Components\FileUpload::make('value')
                            ->label('Image')
                            ->visible(fn(Forms\Get $get): bool => $get('type') === 'image')
                            ->image()
                            ->disk('public')
                            ->directory('website-settings')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'general' => 'primary',
                        'seo' => 'success',
                        'social' => 'info',
                        'contact' => 'warning',
                        'appearance' => 'purple',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color('secondary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->tooltip(function ($record) {
                        if ($record->type === 'boolean') {
                            return $record->value ? 'True' : 'False';
                        }
                        return $record->value;
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type === 'boolean') {
                            return $state ? 'True' : 'False';
                        }
                        if ($record->type === 'image' && $state) {
                            return 'Image uploaded';
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->label('Last Updated')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'seo' => 'SEO',
                        'social' => 'Social Media',
                        'contact' => 'Contact',
                        'appearance' => 'Appearance',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'boolean' => 'Boolean',
                        'number' => 'Number',
                        'url' => 'URL',
                        'email' => 'Email',
                        'image' => 'Image',
                    ]),
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
            ->defaultSort('group')
            ->groups([
                Tables\Grouping\Group::make('group')
                    ->label('Group')
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebsiteSettings::route('/'),
            'create' => Pages\CreateWebsiteSetting::route('/create'),
            'edit' => Pages\EditWebsiteSetting::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
