<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AskAIController extends Controller
{
    /**
     * Display the Ask AI page
     */
    public function index()
    {
        return inertia('ask-ai');
    }

    /**
     * Ask question to AI and get response
     */
    public function askQuestion(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:1000'
        ]);

        $question = $request->input('question');

        try {
            // Call n8n webhook
            $response = Http::timeout(30)->post('https://workflow.wafik.net/webhook-test/58785576-3f79-4a87-a616-5d9a21db7d99', [
                'question' => $question,
                'context' => 'Ini adalah aplikasi untuk data Inisiasi Menyusui Dini (IMD). Data disimpan dalam tabel `imds` dengan kolom: id, nama_ibu, umur_ibu, nama_bayi, jenis_kelamin, tanggal_lahir, berat_badan, cara_persalinan, tempat_persalinan, nama_petugas, waktu_imd, created_at, updated_at, deleted_at. Mohon berikan jawaban yang relevan dengan data IMD atau buatkan query SQL jika diperlukan.'
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghubungi AI service'
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
                } else {
                    // This is a regular text response, not a query
                    $isQuery = false;
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

    /**
     * Execute SQL query safely
     */
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
        $dangerousKeywords = ['DROP', 'UPDATE', 'INSERT', 'ALTER', 'CREATE', 'TRUNCATE', 'EXEC', 'EXECUTE'];
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

    /**
     * Execute query endpoint
     */
    public function executeQueryEndpoint(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string'
        ]);

        $result = $this->executeQuery($request->input('query'));

        return response()->json($result);
    }

    /**
     * Get sample questions for AI
     */
    public function getSampleQuestions(): JsonResponse
    {
        $samples = [
            'Berapa total data IMD yang tercatat?',
            'Tampilkan data IMD untuk bulan ini',
            'Berapa rata-rata durasi IMD?',
            'Tampilkan distribusi cara persalinan',
            'Data IMD dengan durasi paling lama',
            'Berapa ibu yang melakukan IMD lebih dari 60 menit?',
            'Tampilkan trend IMD per bulan',
            'Data ibu yang lahir di rumah sakit',
            'Siapa petugas yang paling sering menangani IMD?',
            'Berapa bayi laki-laki dan perempuan yang sudah IMD?'
        ];

        return response()->json([
            'success' => true,
            'data' => $samples
        ]);
    }
}
