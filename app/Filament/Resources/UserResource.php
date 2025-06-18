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
use pxlrbt\FilamentExcel\Columns\Column;

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

                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->color('info')
                    ->separator(',')
                    ->limit(20),

                Tables\Columns\IconColumn::make('onboarding_completed')
                    ->label('Onboarding')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return $record->hasBadge('Marketers Onboard!');
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('provider')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'google' => 'success',
                        default => 'gray',
                    }),

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
                Tables\Columns\TextColumn::make('badges_earned')
                    ->label('Badges')
                    ->getStateUsing(function ($record) {
                        return $record->badges->pluck('name')->join(', ');
                    })
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_votes_given')
                    ->label('Votes Given')
                    ->getStateUsing(function ($record) {
                        return $record->votes()->count();
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_votes_received')
                    ->label('Votes Received')
                    ->getStateUsing(function ($record) {
                        return $record->posts()->withCount('votes')->get()->sum('votes_count') +
                            $record->answers()->withCount('votes')->get()->sum('votes_count');
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('following_count')
                    ->label('Following')
                    ->getStateUsing(function ($record) {
                        return $record->followings()->count();
                    })
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('last_activity')
                    ->label('Last Activity')
                    ->getStateUsing(function ($record) {
                        $lastPost = $record->posts()->latest()->first()?->created_at;
                        $lastAnswer = $record->answers()->latest()->first()?->created_at;
                        $lastVote = $record->votes()->latest()->first()?->created_at;

                        $activities = collect([$lastPost, $lastAnswer, $lastVote])->filter();

                        return $activities->isEmpty() ? null : $activities->max();
                    })
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('registration_source')
                    ->label('Source')
                    ->getStateUsing(function ($record) {
                        return $record->provider ?? 'email';
                    })
                    ->badge()
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

                Tables\Filters\Filter::make('onboarding_completed')
                    ->query(fn(Builder $query): Builder => $query->whereHas('badges', function ($q) {
                        $q->where('name', 'Marketers Onboard!');
                    }))
                    ->label('Onboarding Completed'),

                Tables\Filters\Filter::make('onboarding_incomplete')
                    ->query(fn(Builder $query): Builder => $query->whereDoesntHave('badges', function ($q) {
                        $q->where('name', 'Marketers Onboard!');
                    }))
                    ->label('Onboarding Incomplete'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn() => 'users-' . date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                            ->withColumns([
                                Column::make('name'),
                                Column::make('email'),
                                Column::make('phone'),
                                Column::make('bio'),
                                Column::make('job_title'),
                                Column::make('company'),
                                Column::make('industry'),
                                Column::make('seniority'),
                                Column::make('company_size'),
                                Column::make('city'),
                                Column::make('provider'),
                                Column::make('reputation'),
                                Column::make('email_verified_at'),
                                Column::make('created_at'),
                                Column::make('updated_at'),
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
                                ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                                ->withColumns([
                                    Column::make('name'),
                                    Column::make('email'),
                                    Column::make('phone'),
                                    Column::make('bio'),
                                    Column::make('job_title'),
                                    Column::make('company'),
                                    Column::make('industry'),
                                    Column::make('seniority'),
                                    Column::make('company_size'),
                                    Column::make('city'),
                                    Column::make('provider'),
                                    Column::make('reputation'),
                                    Column::make('email_verified_at'),
                                    Column::make('created_at'),
                                    Column::make('updated_at'),
                                ]),
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
            ->with(['roles', 'posts', 'answers', 'badges']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
