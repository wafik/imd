<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImdResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_pasien' => $this->nama_pasien,
            'alamat' => $this->alamat,
            'no_rm' => $this->no_rm,
            'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
            'cara_persalinan' => $this->cara_persalinan,
            'tanggal_imd' => $this->tanggal_imd?->format('Y-m-d'),
            'waktu_imd' => $this->waktu_imd,
            'nama_petugas' => $this->nama_petugas,
            'umur' => $this->tanggal_lahir ? now()->diffInYears($this->tanggal_lahir) . ' tahun' : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
