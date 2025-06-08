<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('bio')
                            ->maxLength(500)
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Professional Information')
                    ->schema([
                        Forms\Components\TextInput::make('job_title')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('industry')
                            ->maxLength(255),
                        Forms\Components\Select::make('seniority')
                            ->options([
                                'entry' => 'Entry Level',
                                'junior' => 'Junior',
                                'mid' => 'Mid Level',
                                'senior' => 'Senior',
                                'lead' => 'Lead',
                                'manager' => 'Manager',
                                'director' => 'Director',
                                'executive' => 'Executive',
                            ]),
                        Forms\Components\Select::make('company_size')
                            ->options([
                                '1-10' => '1-10 employees',
                                '11-50' => '11-50 employees',
                                '51-200' => '51-200 employees',
                                '201-500' => '201-500 employees',
                                '501-1000' => '501-1000 employees',
                                '1001+' => '1000+ employees',
                            ]),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('System Information')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload(),
                        Forms\Components\TextInput::make('reputation')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('email_verified_at')
                            ->label('Email Verified')
                            ->afterStateHydrated(function ($component, $state) {
                                $component->state(!!$state);
                            }),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_picture')
                    ->label('Avatar')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(function ($record) {
                        return $record->getProfileImageUrl();
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('job_title')
                    ->label('Job Title')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->job_title;
                    }),

                Tables\Columns\TextColumn::make('company')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(function ($record) {
                        return $record->company;
                    }),

                Tables\Columns\TextColumn::make('industry')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('seniority')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'entry', 'junior' => 'success',
                        'mid', 'senior' => 'warning',
                        'lead', 'manager' => 'info',
                        'director', 'executive' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->icon('heroicon-m-map-pin'),

                Tables\Columns\TextColumn::make('provider')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'google' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->color('info')
                    ->separator(',')
                    ->limit(20),

                Tables\Columns\TextColumn::make('reputation')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'warning',
                        $state >= 100 => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('posts_count')
                    ->counts('posts')
                    ->label('Posts')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('answers_count')
                    ->counts('answers')
                    ->label('Answers')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('followers_count')
                    ->label('Followers')
                    ->getStateUsing(function ($record) {
                        return $record->followers()->count();
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->boolean()
                    ->label('Verified')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->label('Joined')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple(),

                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        'google' => 'Google',
                        null => 'Email',
                    ]),

                Tables\Filters\SelectFilter::make('industry')
                    ->options(function () {
                        return User::whereNotNull('industry')
                            ->distinct()
                            ->pluck('industry', 'industry')
                            ->toArray();
                    }),

                Tables\Filters\SelectFilter::make('seniority')
                    ->options([
                        'entry' => 'Entry Level',
                        'junior' => 'Junior',
                        'mid' => 'Mid Level',
                        'senior' => 'Senior',
                        'lead' => 'Lead',
                        'manager' => 'Manager',
                        'director' => 'Director',
                        'executive' => 'Executive',
                    ]),

                Tables\Filters\Filter::make('verified')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('email_verified_at'))
                    ->label('Email Verified'),

                Tables\Filters\Filter::make('unverified')
                    ->query(fn(Builder $query): Builder => $query->whereNull('email_verified_at'))
                    ->label('Email Unverified'),

                Tables\Filters\Filter::make('has_posts')
                    ->query(fn(Builder $query): Builder => $query->has('posts'))
                    ->label('Has Posts'),

                Tables\Filters\Filter::make('active_users')
                    ->query(fn(Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30)))
                    ->label('Active (30 days)'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn() => 'users-' . date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                            ->withColumns([
                                'name' => 'Name',
                                'email' => 'Email',
                                'phone' => 'Phone',
                                'job_title' => 'Job Title',
                                'company' => 'Company',
                                'industry' => 'Industry',
                                'seniority' => 'Seniority',
                                'company_size' => 'Company Size',
                                'city' => 'City',
                                'provider' => 'Login Provider',
                                'reputation' => 'Reputation',
                                'posts_count' => 'Posts Count',
                                'answers_count' => 'Answers Count',
                                'email_verified_at' => 'Email Verified',
                                'created_at' => 'Joined Date',
                            ]),
                    ])
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete User')
                    ->modalDescription('Are you sure you want to delete this user? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn() => 'selected-users-' . date('Y-m-d'))
                                ->withWriterType(\Maatwebsite\Excel\Excel::XLSX),
                        ]),
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Users')
                        ->modalDescription('Are you sure you want to delete these users? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete them'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['roles', 'posts', 'answers']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
