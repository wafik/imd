<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ImdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_pasien' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_rm' => 'required|string|max:20',
            'tanggal_lahir' => 'required|date|before:today',
            'cara_persalinan' => ['required', Rule::in(['SC', 'Spontan'])],
            'tanggal_imd' => 'required|date',
            'waktu_imd' => ['required', Rule::in(['15 menit', '30 menit', '45 menit', '60 menit'])],
            'nama_petugas' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'nama_pasien.required' => 'Nama pasien wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
            'no_rm.required' => 'Nomor RM wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'cara_persalinan.required' => 'Cara persalinan wajib dipilih.',
            'cara_persalinan.in' => 'Cara persalinan harus SC atau Spontan.',
            'tanggal_imd.required' => 'Tanggal IMD wajib diisi.',
            'waktu_imd.required' => 'Waktu IMD wajib dipilih.',
            'waktu_imd.in' => 'Waktu IMD harus dipilih dari pilihan yang tersedia.',
            'nama_petugas.required' => 'Nama petugas wajib diisi.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
