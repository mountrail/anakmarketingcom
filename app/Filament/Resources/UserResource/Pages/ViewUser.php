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

                Infolists\Components\TextEntry::make('badges_details')
                    ->label('Badge Details')
                    ->getStateUsing(function ($record) {
                        // Ensure badges relationship is loaded with pivot data
                        $badges = $record->badges()->withPivot('earned_at')->get();

                        if ($badges->isEmpty()) {
                            return 'No badges earned yet';
                        }

                        return $badges->map(function ($badge) {
                            $earnedAt = 'Unknown date';
                            if ($badge->pivot->earned_at) {
                                try {
                                    // Handle both string and Carbon date formats
                                    if (is_string($badge->pivot->earned_at)) {
                                        $earnedAt = \Carbon\Carbon::parse($badge->pivot->earned_at)->format('M d, Y');
                                    } else {
                                        $earnedAt = $badge->pivot->earned_at->format('M d, Y');
                                    }
                                } catch (\Exception $e) {
                                    $earnedAt = 'Invalid date';
                                }
                            }
                            return $badge->name . ' - ' . $badge->description . ' (Earned: ' . $earnedAt . ')';
                        })->join("<br>");
                    })
                    ->html()
                    ->columnSpanFull(),

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
