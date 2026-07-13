<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
use App\Models\AdminNotification;
use App\Models\AiRequest;
use App\Models\Media;
use App\Models\Resume;
use App\Models\Subscription;
use App\Models\Template;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdminDashboardService
{
    public function data(User $admin, ?string $from = null, ?string $to = null): array
    {
        $toDate = filled($to) ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
        $fromDate = filled($from) ? Carbon::parse($from)->startOfDay() : $toDate->copy()->subDays(13)->startOfDay();
        if ($fromDate->greaterThan($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }
        if ($fromDate->diffInDays($toDate) > 30) {
            $fromDate = $toDate->copy()->subDays(30)->startOfDay();
        }

        $period = collect(CarbonPeriod::create($fromDate, $toDate));
        $usersByDate = $this->dailyCounts(User::query()->where('created_at', '>=', $period->first()), $period);
        $resumesByDate = $this->dailyCounts(Resume::query()->where('created_at', '>=', $period->first()), $period);
        $storageBytes = (int) Media::query()->sum('size_bytes');

        return [
            'stats' => [
                $this->stat('Total Users', User::query()->count(), User::class, 'users', 'violet'),
                $this->stat('Resumes Created', Resume::query()->count(), Resume::class, 'resumes', 'blue'),
                $this->stat('Templates', Template::query()->count(), Template::class, 'templates', 'emerald'),
                $this->stat('Active Subscriptions', Subscription::query()->whereIn('status', ['active', 'trialing'])->count(), Subscription::class, 'subscriptions', 'orange'),
                $this->stat('Revenue', (int) Transaction::query()->where('status', 'completed')->sum('amount_cents'), Transaction::class, 'transactions', 'rose', true),
            ],
            'chart' => [
                'categories' => $period->map(fn ($date) => $date->format('M j'))->values(),
                'series' => [
                    ['name' => 'Resumes Created', 'data' => $resumesByDate],
                    ['name' => 'User Growth', 'data' => $usersByDate],
                ],
            ],
            'recentUsers' => User::query()->latest()->limit(5)->get(),
            'recentResumes' => Resume::query()->with(['user:id,name', 'template:id,name'])->latest()->limit(5)->get(),
            'notifications' => AdminNotification::query()
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $admin->id)
                ->latest()
                ->limit(4)
                ->get(),
            'activity' => ActivityLog::query()->latest()->limit(5)->get(),
            'system' => [
                'database' => $this->databaseStatus(),
                'storagePercent' => min(100, round(($storageBytes / (10 * 1024 * 1024 * 1024)) * 100)),
                'storageLabel' => $this->formatBytes($storageBytes).' of 10 GB',
                'aiToday' => AiRequest::query()->whereDate('created_at', today())->count(),
                'aiLimit' => 10000,
            ],
            'dateRange' => $fromDate->format('M j').' – '.$toDate->format('M j, Y'),
            'dateFilter' => ['from' => $fromDate->toDateString(), 'to' => $toDate->toDateString()],
        ];
    }

    private function stat(string $label, int $value, string $model, string $route, string $tone, bool $currency = false): array
    {
        $current = $model::query()->where('created_at', '>=', now()->subDays(30))->count();
        $previous = $model::query()->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count();
        $change = $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : ($current > 0 ? 100 : 0);

        return compact('label', 'value', 'route', 'tone', 'currency', 'change');
    }

    private function dailyCounts($query, Collection $period): array
    {
        $counts = $query->get(['created_at'])->groupBy(fn ($record) => $record->created_at->format('Y-m-d'))->map->count();

        return $period->map(fn ($date) => $counts->get($date->format('Y-m-d'), 0))->values()->all();
    }

    private function databaseStatus(): bool
    {
        try {
            DB::select('select 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return number_format($bytes / 1024 / 1024, 1).' MB';
        }

        return number_format($bytes / 1024 / 1024 / 1024, 1).' GB';
    }
}
