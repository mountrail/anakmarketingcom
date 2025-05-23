<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete User')
                ->modalDescription('Are you sure you want to delete this user? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete it'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('User Information')
                    ->schema([
                        ImageEntry::make('profile_picture')
                            ->label('Profile Picture')
                            ->circular()
                            ->defaultImageUrl(function ($record) {
                                return $record->getProfileImageUrl();
                            }),
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                        TextEntry::make('phone'),
                        TextEntry::make('provider')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'google' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('roles.name')
                            ->badge()
                            ->color('info')
                            ->separator(',')
                            ->label('Roles'),
                        TextEntry::make('email_verified_at')
                            ->dateTime()
                            ->label('Email Verified'),
                    ])->columns(2),

                Section::make('Professional Information')
                    ->schema([
                        TextEntry::make('title'),
                        TextEntry::make('industry'),
                        TextEntry::make('seniority'),
                        TextEntry::make('company_size'),
                        TextEntry::make('city'),
                    ])->columns(2),

                Section::make('Activity')
                    ->schema([
                        TextEntry::make('posts_count')
                            ->state(function ($record) {
                                return $record->posts()->count();
                            })
                            ->label('Total Posts'),
                        TextEntry::make('answers_count')
                            ->state(function ($record) {
                                return $record->answers()->count();
                            })
                            ->label('Total Answers'),
                        TextEntry::make('votes_count')
                            ->state(function ($record) {
                                return $record->votes()->count();
                            })
                            ->label('Total Votes'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->label('Joined'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->label('Last Updated'),
                    ])->columns(3),
            ]);
    }
}
