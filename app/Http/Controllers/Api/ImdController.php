<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImdResource;
use App\Models\Imd;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'IMD',
    description: 'IMD (Inisiasi Menyusu Dini) management endpoints'
)]
class ImdController extends Controller
{
    #[OA\Get(
        path: '/api/imds',
        summary: 'Get list of IMD records with filters',
        security: [['bearerAuth' => []]],
        tags: ['IMD'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 10)
            ),
            new OA\Parameter(
                name: 'search',
                description: 'Search in patient name, RM number, or staff name',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'John')
            ),
            new OA\Parameter(
                name: 'cara_persalinan',
                description: 'Filter by delivery method',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['SC', 'Spontan'])
            ),
            new OA\Parameter(
                name: 'waktu_imd',
                description: 'Filter by IMD duration',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['15 menit', '30 menit', '45 menit', '60 menit'])
            ),
            new OA\Parameter(
                name: 'tanggal_lahir_dari',
                description: 'Filter by birth date from',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2023-01-01')
            ),
            new OA\Parameter(
                name: 'tanggal_lahir_sampai',
                description: 'Filter by birth date to',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2023-12-31')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'IMD records retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data IMD berhasil diambil'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Imd')),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                                new OA\Property(property: 'total', type: 'integer', example: 50)
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);

        $imds = Imd::when($request->search, function ($query, $search) {
            $query->where('nama_pasien', 'like', '%' . $search . '%')
                ->orWhere('no_rm', 'like', '%' . $search . '%')
                ->orWhere('nama_petugas', 'like', '%' . $search . '%');
        })
            ->when($request->cara_persalinan, function ($query, $cara) {
                $query->where('cara_persalinan', $cara);
            })
            ->when($request->waktu_imd, function ($query, $waktu) {
                $query->where('waktu_imd', $waktu);
            })
            ->when($request->tanggal_lahir_dari, function ($query, $dari) {
                $query->where('tanggal_lahir', '>=', $dari);
            })
            ->when($request->tanggal_lahir_sampai, function ($query, $sampai) {
                $query->where('tanggal_lahir', '<=', $sampai);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data IMD berhasil diambil',
            'data' => [
                'data' => ImdResource::collection($imds->items()),
                'current_page' => $imds->currentPage(),
                'last_page' => $imds->lastPage(),
                'per_page' => $imds->perPage(),
                'total' => $imds->total(),
                'from' => $imds->firstItem(),
                'to' => $imds->lastItem(),
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/imds',
        summary: 'Create new IMD record',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ImdRequest')
        ),
        tags: ['IMD'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'IMD record created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data IMD berhasil ditambahkan'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Imd')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_pasien' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'no_rm' => 'required|string|max:50|unique:imds,no_rm',
            'tanggal_lahir' => 'required|date|before:today',
            'cara_persalinan' => ['required', Rule::in(['SC', 'Spontan'])],
            'tanggal_imd' => 'required|date',
            'waktu_imd' => ['required', Rule::in(['15 menit', '30 menit', '45 menit', '60 menit'])],
            'nama_petugas' => 'required|string|max:255',
        ], [
            'nama_pasien.required' => 'Nama pasien wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
            'no_rm.required' => 'No RM wajib diisi.',
            'no_rm.unique' => 'No RM sudah digunakan.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'cara_persalinan.required' => 'Cara persalinan wajib dipilih.',
            'tanggal_imd.required' => 'Tanggal IMD wajib diisi.',
            'waktu_imd.required' => 'Waktu IMD wajib dipilih.',
            'nama_petugas.required' => 'Nama petugas wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $imd = Imd::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data IMD berhasil ditambahkan',
            'data' => new ImdResource($imd)
        ], 201);
    }

    #[OA\Get(
        path: '/api/imds/{id}',
        summary: 'Get specific IMD record',
        security: [['bearerAuth' => []]],
        tags: ['IMD'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'IMD ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'IMD record retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data IMD berhasil diambil'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Imd')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'IMD record not found')
        ]
    )]
    public function show(Imd $imd): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data IMD berhasil diambil',
            'data' => new ImdResource($imd)
        ]);
    }

    #[OA\Put(
        path: '/api/imds/{id}',
        summary: 'Update IMD record',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ImdRequest')
        ),
        tags: ['IMD'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'IMD ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'IMD record updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data IMD berhasil diperbarui'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Imd')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'IMD record not found'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function update(Request $request, Imd $imd): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_pasien' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'no_rm' => 'required|string|max:50|unique:imds,no_rm,' . $imd->id,
            'tanggal_lahir' => 'required|date|before:today',
            'cara_persalinan' => ['required', Rule::in(['SC', 'Spontan'])],
            'tanggal_imd' => 'required|date',
            'waktu_imd' => ['required', Rule::in(['15 menit', '30 menit', '45 menit', '60 menit'])],
            'nama_petugas' => 'required|string|max:255',
        ], [
            'nama_pasien.required' => 'Nama pasien wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
            'no_rm.required' => 'No RM wajib diisi.',
            'no_rm.unique' => 'No RM sudah digunakan.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'cara_persalinan.required' => 'Cara persalinan wajib dipilih.',
            'tanggal_imd.required' => 'Tanggal IMD wajib diisi.',
            'waktu_imd.required' => 'Waktu IMD wajib dipilih.',
            'nama_petugas.required' => 'Nama petugas wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $imd->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data IMD berhasil diperbarui',
            'data' => new ImdResource($imd->fresh())
        ]);
    }

    #[OA\Delete(
        path: '/api/imds/{id}',
        summary: 'Delete IMD record',
        security: [['bearerAuth' => []]],
        tags: ['IMD'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'IMD ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'IMD record deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data IMD berhasil dihapus')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'IMD record not found')
        ]
    )]
    public function destroy(Imd $imd): JsonResponse
    {
        $imd->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data IMD berhasil dihapus'
        ]);
    }
}
