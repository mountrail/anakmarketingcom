<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Verified Users', User::whereNotNull('email_verified_at')->count())
                ->description('Users with verified emails')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),

            Stat::make('Google Users', User::where('provider', 'google')->count())
                ->description('Users registered via Google')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('warning'),

            Stat::make('New This Month', User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count())
                ->description('Users registered this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
        ];
    }
}
