<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Imd;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Dashboard',
    description: 'Dashboard analytics endpoints for IMD data visualization and statistics'
)]
class DashboardController extends Controller
{
    #[OA\Get(
        path: '/api/dashboard',
        summary: 'Get comprehensive dashboard analytics data',
        description: 'Returns complete dashboard data including statistics, charts data, and recent records with advanced filtering capabilities for Flutter mobile app consumption',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard']
    )]
    #[OA\Parameter(
        name: 'year',
        description: 'Filter by specific year (e.g., 2024, 2025)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 2025)
    )]
    #[OA\Parameter(
        name: 'month',
        description: 'Filter by specific month (1-12)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 12, example: 8)
    )]
    #[OA\Parameter(
        name: 'cara_persalinan',
        description: 'Filter by delivery method type',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['SC', 'Spontan'], example: 'SC')
    )]
    #[OA\Response(
        response: 200,
        description: 'Dashboard data retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Dashboard data retrieved successfully'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'stats',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total_imd', type: 'integer', example: 150),
                                new OA\Property(property: 'sc_percentage', type: 'number', format: 'float', example: 35.5),
                                new OA\Property(property: 'spontan_percentage', type: 'number', format: 'float', example: 64.5),
                                new OA\Property(property: 'avg_duration', type: 'number', format: 'float', example: 42.5)
                            ]
                        ),
                        new OA\Property(
                            property: 'charts',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'imd_by_cara_persalinan',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'name', type: 'string', example: 'SC'),
                                            new OA\Property(property: 'value', type: 'integer', example: 45),
                                            new OA\Property(property: 'color', type: 'string', example: '#ef4444')
                                        ]
                                    )
                                ),
                                new OA\Property(
                                    property: 'imd_by_waktu',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'name', type: 'string', example: '30 menit'),
                                            new OA\Property(property: 'value', type: 'integer', example: 25),
                                            new OA\Property(property: 'color', type: 'string', example: '#f59e0b')
                                        ]
                                    )
                                ),
                                new OA\Property(
                                    property: 'monthly_trend',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'month', type: 'string', example: 'Jan 2025'),
                                            new OA\Property(property: 'value', type: 'integer', example: 12)
                                        ]
                                    )
                                ),
                                new OA\Property(
                                    property: 'age_distribution',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'name', type: 'string', example: '26-30 tahun'),
                                            new OA\Property(property: 'value', type: 'integer', example: 35)
                                        ]
                                    )
                                )
                            ]
                        ),
                        new OA\Property(
                            property: 'recent_imds',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', example: '01HXYZ123456789'),
                                    new OA\Property(property: 'nama_pasien', type: 'string', example: 'Siti Nurhaliza'),
                                    new OA\Property(property: 'no_rm', type: 'string', example: 'RM001234'),
                                    new OA\Property(property: 'cara_persalinan', type: 'string', example: 'SC'),
                                    new OA\Property(property: 'waktu_imd', type: 'string', example: '30 menit'),
                                    new OA\Property(property: 'tanggal_imd', type: 'string', format: 'date', example: '2025-08-11'),
                                    new OA\Property(property: 'nama_petugas', type: 'string', example: 'Dr. Ahmad')
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'filters',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'year', type: 'integer', example: 2025),
                                new OA\Property(property: 'month', type: 'integer', example: 8),
                                new OA\Property(property: 'cara_persalinan', type: 'string', example: 'SC')
                            ]
                        ),
                        new OA\Property(
                            property: 'available_years',
                            type: 'array',
                            items: new OA\Items(type: 'integer'),
                            example: [2020, 2021, 2022, 2023, 2024, 2025]
                        )
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized access - invalid or missing token',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error - invalid filter parameters',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Validation failed'),
                new OA\Property(property: 'errors', type: 'object')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Failed to retrieve dashboard data')
            ]
        )
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['year', 'month', 'cara_persalinan']);

            // Default to current year if no year filter
            $year = $filters['year'] ?? Carbon::now()->year;
            $month = $filters['month'] ?? null;
            $caraPersalinan = $filters['cara_persalinan'] ?? null;

            // Validate filters
            $request->validate([
                'year' => 'nullable|integer|min:2020|max:' . (Carbon::now()->year + 5),
                'month' => 'nullable|integer|min:1|max:12',
                'cara_persalinan' => 'nullable|in:SC,Spontan'
            ]);

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
                ->select([
                    'id',
                    'nama_pasien',
                    'no_rm',
                    'cara_persalinan',
                    'waktu_imd',
                    'tanggal_imd',
                    'nama_petugas'
                ])
                ->get();

            // Stats cards
            $stats = [
                'total_imd' => $totalImd,
                'sc_percentage' => $totalImd > 0 ? round(($imdByCaraPersalinan->where('name', 'SC')->first()->value ?? 0) / $totalImd * 100, 1) : 0,
                'spontan_percentage' => $totalImd > 0 ? round(($imdByCaraPersalinan->where('name', 'Spontan')->first()->value ?? 0) / $totalImd * 100, 1) : 0,
                'avg_duration' => round((clone $baseQuery)->avg(DB::raw('CAST(SUBSTRING_INDEX(waktu_imd, " ", 1) AS UNSIGNED)')) ?? 0, 1)
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard data retrieved successfully',
                'data' => [
                    'stats' => $stats,
                    'charts' => [
                        'imd_by_cara_persalinan' => $imdByCaraPersalinan->values(),
                        'imd_by_waktu' => $imdByWaktu->values(),
                        'monthly_trend' => $monthlyTrend,
                        'age_distribution' => $ageDistribution->values()
                    ],
                    'recent_imds' => $recentImds,
                    'filters' => $filters,
                    'available_years' => range(2020, Carbon::now()->year)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: '/api/dashboard/stats',
        summary: 'Get dashboard statistics only',
        description: 'Returns lightweight statistics data for quick dashboard updates and overview cards',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard']
    )]
    #[OA\Parameter(
        name: 'year',
        description: 'Filter statistics by specific year',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 2025)
    )]
    #[OA\Parameter(
        name: 'month',
        description: 'Filter statistics by specific month (1-12)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 12, example: 8)
    )]
    #[OA\Parameter(
        name: 'cara_persalinan',
        description: 'Filter statistics by delivery method',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['SC', 'Spontan'], example: 'SC')
    )]
    #[OA\Response(
        response: 200,
        description: 'Statistics retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Statistics retrieved successfully'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'total_imd', type: 'integer', example: 150, description: 'Total number of IMD records'),
                        new OA\Property(property: 'sc_percentage', type: 'number', format: 'float', example: 35.5, description: 'Percentage of SC deliveries'),
                        new OA\Property(property: 'spontan_percentage', type: 'number', format: 'float', example: 64.5, description: 'Percentage of spontaneous deliveries'),
                        new OA\Property(property: 'avg_duration', type: 'number', format: 'float', example: 42.5, description: 'Average IMD duration in minutes')
                    ]
                )
            ]
        )
    )]
    public function stats(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['year', 'month', 'cara_persalinan']);

            $year = $filters['year'] ?? Carbon::now()->year;
            $month = $filters['month'] ?? null;
            $caraPersalinan = $filters['cara_persalinan'] ?? null;

            // Validate filters
            $request->validate([
                'year' => 'nullable|integer|min:2020|max:' . (Carbon::now()->year + 5),
                'month' => 'nullable|integer|min:1|max:12',
                'cara_persalinan' => 'nullable|in:SC,Spontan'
            ]);

            // Base query with filters
            $baseQuery = Imd::whereYear('tanggal_imd', $year);

            if ($month) {
                $baseQuery->whereMonth('tanggal_imd', $month);
            }

            if ($caraPersalinan) {
                $baseQuery->where('cara_persalinan', $caraPersalinan);
            }

            $totalImd = (clone $baseQuery)->count();

            // Get delivery method counts for percentage calculation
            $scCount = (clone $baseQuery)->where('cara_persalinan', 'SC')->count();
            $spontanCount = (clone $baseQuery)->where('cara_persalinan', 'Spontan')->count();

            $stats = [
                'total_imd' => $totalImd,
                'sc_percentage' => $totalImd > 0 ? round($scCount / $totalImd * 100, 1) : 0,
                'spontan_percentage' => $totalImd > 0 ? round($spontanCount / $totalImd * 100, 1) : 0,
                'avg_duration' => round((clone $baseQuery)->avg(DB::raw('CAST(SUBSTRING_INDEX(waktu_imd, " ", 1) AS UNSIGNED)')) ?? 0, 1)
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Statistics retrieved successfully',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: '/api/dashboard/charts',
        summary: 'Get dashboard charts data',
        description: 'Returns charts data for visualization with optional filtering by chart type for optimized API calls',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard']
    )]
    #[OA\Parameter(
        name: 'year',
        description: 'Filter charts data by specific year',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', example: 2025)
    )]
    #[OA\Parameter(
        name: 'month',
        description: 'Filter charts data by specific month (1-12)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 12, example: 8)
    )]
    #[OA\Parameter(
        name: 'cara_persalinan',
        description: 'Filter charts data by delivery method',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['SC', 'Spontan'], example: 'SC')
    )]
    #[OA\Parameter(
        name: 'chart_type',
        description: 'Request specific chart type only for optimized performance',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['cara_persalinan', 'waktu', 'monthly_trend', 'age_distribution'], example: 'cara_persalinan')
    )]
    #[OA\Response(
        response: 200,
        description: 'Charts data retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Charts data retrieved successfully'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'imd_by_cara_persalinan',
                            type: 'array',
                            description: 'IMD distribution by delivery method',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'name', type: 'string', example: 'SC'),
                                    new OA\Property(property: 'value', type: 'integer', example: 45),
                                    new OA\Property(property: 'color', type: 'string', example: '#ef4444')
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'imd_by_waktu',
                            type: 'array',
                            description: 'IMD distribution by duration',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'name', type: 'string', example: '30 menit'),
                                    new OA\Property(property: 'value', type: 'integer', example: 25),
                                    new OA\Property(property: 'color', type: 'string', example: '#f59e0b')
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'monthly_trend',
                            type: 'array',
                            description: 'IMD trend over last 12 months',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'month', type: 'string', example: 'Jan 2025'),
                                    new OA\Property(property: 'value', type: 'integer', example: 12)
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'age_distribution',
                            type: 'array',
                            description: 'Patient age distribution',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'name', type: 'string', example: '26-30 tahun'),
                                    new OA\Property(property: 'value', type: 'integer', example: 35)
                                ]
                            )
                        )
                    ]
                )
            ]
        )
    )]
    public function charts(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['year', 'month', 'cara_persalinan', 'chart_type']);

            $year = $filters['year'] ?? Carbon::now()->year;
            $month = $filters['month'] ?? null;
            $caraPersalinan = $filters['cara_persalinan'] ?? null;
            $chartType = $filters['chart_type'] ?? null;

            // Validate filters
            $request->validate([
                'year' => 'nullable|integer|min:2020|max:' . (Carbon::now()->year + 5),
                'month' => 'nullable|integer|min:1|max:12',
                'cara_persalinan' => 'nullable|in:SC,Spontan',
                'chart_type' => 'nullable|in:cara_persalinan,waktu,monthly_trend,age_distribution'
            ]);

            $charts = [];

            // Base query with filters
            $baseQuery = Imd::whereYear('tanggal_imd', $year);

            if ($month) {
                $baseQuery->whereMonth('tanggal_imd', $month);
            }

            if ($caraPersalinan) {
                $baseQuery->where('cara_persalinan', $caraPersalinan);
            }

            // IMD by delivery method
            if (!$chartType || $chartType === 'cara_persalinan') {
                $charts['imd_by_cara_persalinan'] = Imd::whereYear('tanggal_imd', $year)
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
                    })->values();
            }

            // IMD by duration
            if (!$chartType || $chartType === 'waktu') {
                $charts['imd_by_waktu'] = (clone $baseQuery)
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
                    })->values();
            }

            // Monthly trend
            if (!$chartType || $chartType === 'monthly_trend') {
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
                $charts['monthly_trend'] = $monthlyTrend;
            }

            // Age distribution
            if (!$chartType || $chartType === 'age_distribution') {
                $charts['age_distribution'] = (clone $baseQuery)
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
                    })->values();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Charts data retrieved successfully',
                'data' => $charts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve charts data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
