<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Post;
use App\Models\Answer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class UserStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // Calculate user statistics
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $googleUsers = User::where('provider', 'google')->count();

        // New users this month
        $newThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // New users last month for comparison
        $newLastMonth = User::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        // Calculate percentage change for new users
        $newUsersChange = $newLastMonth > 0
            ? (($newThisMonth - $newLastMonth) / $newLastMonth) * 100
            : ($newThisMonth > 0 ? 100 : 0);

        // Active users (users who posted or answered in last 30 days)
        $activeUsers = User::where(function ($query) {
            $query->whereHas('posts', function ($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })->orWhereHas('answers', function ($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            });
        })->count();

        // Users with high reputation (500+)
        $highRepUsers = User::where('reputation', '>=', 500)->count();

        // Average reputation
        $avgReputation = User::avg('reputation') ?? 0;

        // Top industries
        $topIndustry = User::select('industry', DB::raw('count(*) as total'))
            ->whereNotNull('industry')
            ->groupBy('industry')
            ->orderBy('total', 'desc')
            ->first();

        return [
            // Total Users
            Stat::make('Total Users', number_format($totalUsers))
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart($this->getUserGrowthChart()),

            // Verified Users
            Stat::make('Verified Users', number_format($verifiedUsers))
                ->description(sprintf('%.1f%% of total users', $totalUsers > 0 ? ($verifiedUsers / $totalUsers) * 100 : 0))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),

            // New Users This Month
            Stat::make('New This Month', number_format($newThisMonth))
                ->description($this->getChangeDescription($newUsersChange) . ' from last month')
                ->descriptionIcon($newUsersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($newUsersChange >= 0 ? 'success' : 'danger'),

            // Active Users
            Stat::make('Active Users (30d)', number_format($activeUsers))
                ->description(sprintf('%.1f%% engagement rate', $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0))
                ->descriptionIcon('heroicon-m-bolt')
                ->color('warning'),

            // Google Sign-ups
            Stat::make('Google Sign-ups', number_format($googleUsers))
                ->description(sprintf('%.1f%% use Google auth', $totalUsers > 0 ? ($googleUsers / $totalUsers) * 100 : 0))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('gray'),

            // High Reputation Users
            Stat::make('High Rep Users', number_format($highRepUsers))
                ->description('Users with 500+ reputation')
                ->descriptionIcon('heroicon-m-star')
                ->color('amber'),

            // Average Reputation
            Stat::make('Avg Reputation', number_format($avgReputation, 1))
                ->description('Across all users')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('blue'),

            // Top Industry
            Stat::make('Top Industry', $topIndustry ? $topIndustry->industry : 'N/A')
                ->description($topIndustry ? number_format($topIndustry->total) . ' users' : 'No data')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('purple'),
        ];
    }

    /**
     * Get user growth chart data for the last 7 days
     */
    private function getUserGrowthChart(): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return $data;
    }

    /**
     * Format percentage change description
     */
    private function getChangeDescription(float $change): string
    {
        if ($change == 0) {
            return 'No change';
        } elseif ($change > 0) {
            return '+' . number_format(abs($change), 1) . '%';
        } else {
            return '-' . number_format(abs($change), 1) . '%';
        }
    }

    /**
     * Get the columns span for this widget
     */
    protected function getColumns(): int
    {
        return 4; // Display 4 stats per row
    }

    /**
     * Cache the results for better performance
     */
    protected static bool $isLazy = false;
}
