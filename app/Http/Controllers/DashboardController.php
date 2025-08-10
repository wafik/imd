<?php

namespace App\Http\Controllers;

use App\Models\Imd;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['year', 'month', 'cara_persalinan']);

        // Default to current year if no year filter
        $year = $filters['year'] ?? Carbon::now()->year;
        $month = $filters['month'] ?? null;
        $caraPersalinan = $filters['cara_persalinan'] ?? null;

        // Base query with filters
        $baseQuery = Imd::whereYear('tanggal_imd', $year);

        if ($month) {
            $baseQuery->whereMonth('tanggal_imd', $month);
        }

        if ($caraPersalinan) {
            $baseQuery->where('cara_persalinan', $caraPersalinan);
        }

        // Total IMD count
        $totalImd = (clone $baseQuery)->count();

        // IMD by delivery method
        $imdByCaraPersalinan = Imd::whereYear('tanggal_imd', $year)
            ->when($month, function ($query) use ($month) {
                $query->whereMonth('tanggal_imd', $month);
            })
            ->selectRaw('cara_persalinan, COUNT(*) as count')
            ->groupBy('cara_persalinan')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->cara_persalinan,
                    'value' => $item->count,
                    'color' => $item->cara_persalinan === 'SC' ? '#ef4444' : '#10b981'
                ];
            });

        // IMD by duration
        $imdByWaktu = (clone $baseQuery)
            ->selectRaw('waktu_imd, COUNT(*) as count')
            ->groupBy('waktu_imd')
            ->get()
            ->map(function ($item) {
                $colors = [
                    '15 menit' => '#ef4444',
                    '30 menit' => '#f59e0b',
                    '45 menit' => '#3b82f6',
                    '60 menit' => '#10b981'
                ];
                return [
                    'name' => $item->waktu_imd,
                    'value' => $item->count,
                    'color' => $colors[$item->waktu_imd] ?? '#6b7280'
                ];
            });

        // Monthly trend (last 12 months)
        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Imd::whereYear('tanggal_imd', $date->year)
                ->whereMonth('tanggal_imd', $date->month)
                ->when($caraPersalinan, function ($query) use ($caraPersalinan) {
                    $query->where('cara_persalinan', $caraPersalinan);
                })
                ->count();

            $monthlyTrend[] = [
                'month' => $date->format('M Y'),
                'value' => $count
            ];
        }

        // Age distribution (based on birth date)
        $ageDistribution = (clone $baseQuery)
            ->selectRaw('
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, tanggal_imd) < 20 THEN "< 20 tahun"
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, tanggal_imd) BETWEEN 20 AND 25 THEN "20-25 tahun"
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, tanggal_imd) BETWEEN 26 AND 30 THEN "26-30 tahun"
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, tanggal_imd) BETWEEN 31 AND 35 THEN "31-35 tahun"
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, tanggal_imd) BETWEEN 36 AND 40 THEN "36-40 tahun"
                    ELSE "> 40 tahun"
                END as age_group,
                COUNT(*) as count
            ')
            ->groupBy('age_group')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->age_group,
                    'value' => $item->count
                ];
            });

        // Recent IMD records
        $recentImds = (clone $baseQuery)
            ->latest('tanggal_imd')
            ->limit(5)
            ->get();

        // Stats cards
        $stats = [
            'total_imd' => $totalImd,
            'sc_percentage' => $totalImd > 0 ? round(($imdByCaraPersalinan->where('name', 'SC')->first()->value ?? 0) / $totalImd * 100, 1) : 0,
            'spontan_percentage' => $totalImd > 0 ? round(($imdByCaraPersalinan->where('name', 'Spontan')->first()->value ?? 0) / $totalImd * 100, 1) : 0,
            'avg_duration' => (clone $baseQuery)->avg(DB::raw('CAST(SUBSTRING_INDEX(waktu_imd, " ", 1) AS UNSIGNED)')) ?? 0
        ];

        return Inertia::render('dashboard', [
            'chartData' => [
                'imdByCaraPersalinan' => $imdByCaraPersalinan,
                'imdByWaktu' => $imdByWaktu,
                'monthlyTrend' => $monthlyTrend,
                'ageDistribution' => $ageDistribution
            ],
            'stats' => $stats,
            'recentImds' => $recentImds,
            'filters' => $filters,
            'availableYears' => range(2020, Carbon::now()->year)
        ]);
    }
}
