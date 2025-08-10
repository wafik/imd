<?php

namespace App\Http\Controllers;

use App\Exports\ImdExport;
use App\Models\Imd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImdController extends Controller
{
    public function index(): Response
    {
        $imds = Imd::when(request('search'), function ($query, $search) {
            $query->where('nama_pasien', 'like', '%' . $search . '%')
                ->orWhere('no_rm', 'like', '%' . $search . '%')
                ->orWhere('nama_petugas', 'like', '%' . $search . '%');
        })
            ->when(request('cara_persalinan'), function ($query, $cara) {
                $query->where('cara_persalinan', $cara);
            })
            ->when(request('waktu_imd'), function ($query, $waktu) {
                $query->where('waktu_imd', $waktu);
            })
            ->when(request('tanggal_lahir_dari'), function ($query, $dari) {
                $query->where('tanggal_lahir', '>=', $dari);
            })
            ->when(request('tanggal_lahir_sampai'), function ($query, $sampai) {
                $query->where('tanggal_lahir', '<=', $sampai);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('imd/index', [
            'imds' => $imds,
            'filters' => request()->only(['search', 'cara_persalinan', 'waktu_imd', 'tanggal_lahir_dari', 'tanggal_lahir_sampai']),
        ]);
    }

    public function show(Imd $imd): Response
    {
        return Inertia::render('imd/show', [
            'imd' => $imd,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
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
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'cara_persalinan.required' => 'Cara persalinan wajib dipilih.',
            'tanggal_imd.required' => 'Tanggal IMD wajib diisi.',
            'waktu_imd.required' => 'Waktu IMD wajib dipilih.',
            'nama_petugas.required' => 'Nama petugas wajib diisi.',
        ]);

        Imd::create($validated);

        return redirect()->route('imds.index')->with('success', 'Data IMD berhasil ditambahkan!');
    }

    public function update(Request $request, Imd $imd): RedirectResponse
    {
        $validated = $request->validate([
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

        $imd->update($validated);

        return redirect()->route('imds.index')->with('success', 'Data IMD berhasil diperbarui!');
    }

    public function destroy(Imd $imd): RedirectResponse
    {
        $imd->delete();

        return redirect()->route('imds.index')->with('success', 'Data IMD berhasil dihapus!');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['search', 'cara_persalinan', 'waktu_imd', 'tanggal_lahir_dari', 'tanggal_lahir_sampai']);

        $filename = 'data-imd-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new ImdExport($filters), $filename);
    }
}
