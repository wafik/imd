<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Ask AI',
    description: 'AI-powered query and question answering system for IMD data analysis'
)]
class AskAIController extends Controller
{
    #[OA\Post(
        path: '/api/ask-ai/question',
        summary: 'Ask AI a question about IMD data',
        description: 'Submit a natural language question to AI and get intelligent responses. AI can provide direct answers or generate SQL queries for data analysis.',
        security: [['bearerAuth' => []]],
        tags: ['Ask AI']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['question'],
            properties: [
                new OA\Property(
                    property: 'question',
                    type: 'string',
                    maxLength: 1000,
                    description: 'Natural language question about IMD data',
                    example: 'Berapa total data IMD yang tercatat bulan ini?'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'AI response received successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'question', type: 'string', example: 'Berapa total data IMD yang tercatat?'),
                new OA\Property(property: 'is_query', type: 'boolean', example: false, description: 'Whether the response contains SQL query execution'),
                new OA\Property(property: 'answer', type: 'string', example: 'Berdasarkan data yang tersedia, total data IMD yang tercatat adalah 150 records.'),
                new OA\Property(
                    property: 'query_result',
                    type: 'object',
                    nullable: true,
                    description: 'Query execution results if AI generated SQL query',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(type: 'object'),
                            example: [['count' => 150]]
                        ),
                        new OA\Property(property: 'count', type: 'integer', example: 1),
                        new OA\Property(property: 'query', type: 'string', example: 'SELECT COUNT(*) as count FROM imds WHERE deleted_at IS NULL')
                    ]
                ),
                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2025-08-11T10:30:00.000000Z')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - validation failed',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'The question field is required.'),
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'question',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['The question field is required.']
                        )
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized access',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Server error - AI service unavailable or internal error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Terjadi kesalahan saat menghubungi AI'),
                new OA\Property(property: 'error', type: 'string', example: 'Connection timeout to AI service')
            ]
        )
    )]
    public function askQuestion(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:1000'
        ]);

        $question = $request->input('question');

        try {
            // Call n8n webhook with updated context
            $response = Http::timeout(30)->post('https://workflow.wafik.net/webhook/imd-ai', [
                'question' => $question,
                'context' => 'Ini adalah aplikasi untuk data Inisiasi Menyusui Dini (IMD). Data disimpan dalam tabel `imds` dengan kolom: id, nama_pasien, alamat, no_rm, tanggal_lahir, cara_persalinan, tanggal_imd, waktu_imd, nama_petugas, created_at, updated_at, deleted_at. Mohon berikan jawaban yang relevan dengan data IMD atau buatkan query SQL jika diperlukan.'
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghubungi AI service',
                    'error' => 'HTTP ' . $response->status()
                ], 500);
            }

            $aiResponse = $response->json();

            // Check if response contains SQL query
            $isQuery = false;
            $queryResult = null;
            $answer = $aiResponse['output'] ?? 'Respons AI tidak dapat diparsing';

            // Check if the AI response output contains a SELECT query
            if (isset($aiResponse['output']) && !empty($aiResponse['output'])) {
                $upperOutput = strtoupper($aiResponse['output']);

                if (str_contains($upperOutput, 'SELECT') && str_contains($upperOutput, 'FROM')) {
                    // This is a query, execute it
                    $isQuery = true;
                    $queryResult = $this->executeQuery($aiResponse['output']);

                    // If query execution successful, provide user-friendly message
                    if ($queryResult && $queryResult['success']) {
                        $recordCount = $queryResult['count'] ?? 0;
                        $answer = "Data berhasil diambil. Ditemukan {$recordCount} record yang sesuai dengan pertanyaan Anda.";
                    } else {
                        $answer = "Maaf, terjadi kesalahan saat mengambil data: " . ($queryResult['error'] ?? 'Unknown error');
                    }
                } else {
                    // This is a regular text response, keep the original answer
                    $isQuery = false;
                    $answer = $aiResponse['output'];
                }
            }

            $result = [
                'success' => true,
                'question' => $question,
                'is_query' => $isQuery,
                'answer' => $answer,
                'query_result' => $queryResult,
                'timestamp' => now()->toISOString()
            ];

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungi AI',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/ask-ai/execute-query',
        summary: 'Execute SQL query directly',
        description: 'Execute a SQL SELECT query directly against the IMD database. Only SELECT queries are allowed for security.',
        security: [['bearerAuth' => []]],
        tags: ['Ask AI']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['query'],
            properties: [
                new OA\Property(
                    property: 'query',
                    type: 'string',
                    description: 'SQL SELECT query to execute',
                    example: 'SELECT COUNT(*) as total FROM imds WHERE cara_persalinan = "SC"'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Query executed successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    example: [['total' => 45], ['total' => 67]]
                ),
                new OA\Property(property: 'count', type: 'integer', example: 2, description: 'Number of records returned'),
                new OA\Property(property: 'query', type: 'string', example: 'SELECT COUNT(*) as total FROM imds WHERE cara_persalinan = "SC"')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - invalid query or security violation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'error', type: 'string', example: 'Hanya query SELECT yang diizinkan untuk keamanan'),
                new OA\Property(property: 'query', type: 'string', example: 'UPDATE imds SET...')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Query execution error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'error', type: 'string', example: 'Error executing query: Unknown column "invalid_column"'),
                new OA\Property(property: 'query', type: 'string', example: 'SELECT invalid_column FROM imds')
            ]
        )
    )]
    public function executeQuery(string $query = null): array
    {
        if (!$query) {
            return [
                'success' => false,
                'error' => 'Query tidak boleh kosong'
            ];
        }

        // Security check: only allow SELECT queries
        $trimmedQuery = trim(strtoupper($query));
        if (!str_starts_with($trimmedQuery, 'SELECT')) {
            return [
                'success' => false,
                'error' => 'Hanya query SELECT yang diizinkan untuk keamanan'
            ];
        }

        // Additional security: prevent dangerous keywords
        $dangerousKeywords = ['DROP', 'UPDATE', 'INSERT', 'ALTER', 'CREATE', 'TRUNCATE', 'EXEC', 'EXECUTE', 'DELETE'];
        foreach ($dangerousKeywords as $keyword) {
            if (str_contains($trimmedQuery, $keyword)) {
                return [
                    'success' => false,
                    'error' => "Query mengandung keyword berbahaya: {$keyword}"
                ];
            }
        }

        try {
            $results = DB::select($query);

            return [
                'success' => true,
                'data' => $results,
                'count' => count($results),
                'query' => $query
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error executing query: ' . $e->getMessage(),
                'query' => $query
            ];
        }
    }

    #[OA\Post(
        path: '/api/ask-ai/execute-query-endpoint',
        summary: 'Execute query endpoint wrapper',
        description: 'HTTP endpoint wrapper for executing SQL queries with proper request validation',
        security: [['bearerAuth' => []]],
        tags: ['Ask AI']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['query'],
            properties: [
                new OA\Property(
                    property: 'query',
                    type: 'string',
                    description: 'SQL SELECT query to execute',
                    example: 'SELECT nama_pasien, cara_persalinan FROM imds LIMIT 10'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Query executed successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    example: [
                        ['nama_pasien' => 'Siti Nurhaliza', 'cara_persalinan' => 'SC'],
                        ['nama_pasien' => 'Dewi Sartika', 'cara_persalinan' => 'Spontan']
                    ]
                ),
                new OA\Property(property: 'count', type: 'integer', example: 2),
                new OA\Property(property: 'query', type: 'string', example: 'SELECT nama_pasien, cara_persalinan FROM imds LIMIT 10')
            ]
        )
    )]
    public function executeQueryEndpoint(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string'
        ]);

        $result = $this->executeQuery($request->input('query'));

        if ($result['success']) {
            return response()->json($result);
        } else {
            return response()->json($result, 400);
        }
    }

    #[OA\Get(
        path: '/api/ask-ai/samples',
        summary: 'Get sample questions for AI',
        description: 'Retrieve a list of sample questions that users can ask to the AI about IMD data',
        security: [['bearerAuth' => []]],
        tags: ['Ask AI']
    )]
    #[OA\Response(
        response: 200,
        description: 'Sample questions retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: [
                        'Berapa total data IMD yang tercatat?',
                        'Tampilkan data IMD untuk bulan ini',
                        'Berapa rata-rata durasi IMD?',
                        'Tampilkan distribusi cara persalinan',
                        'Data IMD dengan durasi paling lama'
                    ]
                ),
                new OA\Property(property: 'count', type: 'integer', example: 10, description: 'Number of sample questions')
            ]
        )
    )]
    public function getSampleQuestions(): JsonResponse
    {
        $samples = [
            'Berapa total data IMD yang tercatat?',
            'Tampilkan data IMD untuk bulan ini',
            'Berapa rata-rata durasi IMD?',
            'Tampilkan distribusi cara persalinan',
            'Data IMD dengan durasi paling lama',
            'Berapa ibu yang melakukan IMD lebih dari 60 menit?',
            'Tampilkan trend IMD per bulan tahun ini',
            'Siapa petugas yang paling sering menangani IMD?',
            'Berapa persentase persalinan SC vs Spontan?',
            'Tampilkan 5 data IMD terbaru',
            'Berapa rata-rata waktu IMD berdasarkan cara persalinan?',
            'Data IMD dengan waktu kurang dari 30 menit',
            'Tampilkan jumlah IMD per petugas medis',
            'Berapa total IMD bulan lalu dibanding bulan ini?',
            'Data pasien yang lahir hari ini'
        ];

        return response()->json([
            'success' => true,
            'data' => $samples,
            'count' => count($samples)
        ]);
    }

    #[OA\Get(
        path: '/api/ask-ai/schema',
        summary: 'Get database schema information',
        description: 'Retrieve database schema information for the IMD table to help with query construction',
        security: [['bearerAuth' => []]],
        tags: ['Ask AI']
    )]
    #[OA\Response(
        response: 200,
        description: 'Database schema retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'table_name', type: 'string', example: 'imds'),
                        new OA\Property(
                            property: 'columns',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'name', type: 'string', example: 'nama_pasien'),
                                    new OA\Property(property: 'type', type: 'string', example: 'varchar(255)'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Nama lengkap pasien')
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'sample_queries',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: [
                                'SELECT COUNT(*) FROM imds',
                                'SELECT cara_persalinan, COUNT(*) FROM imds GROUP BY cara_persalinan'
                            ]
                        )
                    ]
                )
            ]
        )
    )]
    public function getSchema(): JsonResponse
    {
        $schema = [
            'table_name' => 'imds',
            'columns' => [
                [
                    'name' => 'id',
                    'type' => 'char(26)',
                    'description' => 'ULID primary key'
                ],
                [
                    'name' => 'nama_pasien',
                    'type' => 'varchar(255)',
                    'description' => 'Nama lengkap pasien/ibu'
                ],
                [
                    'name' => 'alamat',
                    'type' => 'text',
                    'description' => 'Alamat lengkap pasien'
                ],
                [
                    'name' => 'no_rm',
                    'type' => 'varchar(50)',
                    'description' => 'Nomor rekam medis'
                ],
                [
                    'name' => 'tanggal_lahir',
                    'type' => 'date',
                    'description' => 'Tanggal lahir bayi'
                ],
                [
                    'name' => 'cara_persalinan',
                    'type' => 'enum(SC,Spontan)',
                    'description' => 'Metode persalinan: SC (Sectio Caesarea) atau Spontan'
                ],
                [
                    'name' => 'tanggal_imd',
                    'type' => 'date',
                    'description' => 'Tanggal pelaksanaan IMD'
                ],
                [
                    'name' => 'waktu_imd',
                    'type' => 'enum(15 menit,30 menit,45 menit,60 menit)',
                    'description' => 'Durasi pelaksanaan IMD'
                ],
                [
                    'name' => 'nama_petugas',
                    'type' => 'varchar(255)',
                    'description' => 'Nama petugas medis yang menangani'
                ],
                [
                    'name' => 'created_at',
                    'type' => 'timestamp',
                    'description' => 'Waktu pembuatan record'
                ],
                [
                    'name' => 'updated_at',
                    'type' => 'timestamp',
                    'description' => 'Waktu update terakhir record'
                ],
                [
                    'name' => 'deleted_at',
                    'type' => 'timestamp nullable',
                    'description' => 'Waktu soft delete (NULL jika aktif)'
                ]
            ],
            'sample_queries' => [
                'SELECT COUNT(*) as total FROM imds WHERE deleted_at IS NULL',
                'SELECT cara_persalinan, COUNT(*) as jumlah FROM imds GROUP BY cara_persalinan',
                'SELECT waktu_imd, COUNT(*) as jumlah FROM imds GROUP BY waktu_imd',
                'SELECT nama_petugas, COUNT(*) as jumlah_pasien FROM imds GROUP BY nama_petugas',
                'SELECT DATE(tanggal_imd) as tanggal, COUNT(*) as jumlah FROM imds GROUP BY DATE(tanggal_imd)',
                'SELECT * FROM imds WHERE tanggal_imd >= CURDATE() - INTERVAL 30 DAY',
                'SELECT AVG(CAST(SUBSTRING_INDEX(waktu_imd, " ", 1) AS UNSIGNED)) as rata_rata_menit FROM imds'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $schema
        ]);
    }
}
