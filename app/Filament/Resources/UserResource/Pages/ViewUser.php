<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Basic Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('email'),
                        Infolists\Components\TextEntry::make('phone'),
                        Infolists\Components\TextEntry::make('bio')
                            ->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make('Professional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('job_title'),
                        Infolists\Components\TextEntry::make('company'),
                        Infolists\Components\TextEntry::make('industry'),
                        Infolists\Components\TextEntry::make('seniority'),
                        Infolists\Components\TextEntry::make('company_size'),
                        Infolists\Components\TextEntry::make('city'),
                    ])->columns(2),

                Infolists\Components\Section::make('Badges')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('badges')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->badge()
                                    ->color('success'),
                                Infolists\Components\TextEntry::make('description'),
                                Infolists\Components\TextEntry::make('pivot.earned_at')
                                    ->label('Earned At')
                                    ->dateTime(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('System Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('roles.name')
                            ->badge()
                            ->separator(','),
                        Infolists\Components\TextEntry::make('reputation')
                            ->badge(),
                        Infolists\Components\IconEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('provider'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }
}
