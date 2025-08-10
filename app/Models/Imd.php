<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Imd extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'nama_pasien',
        'alamat',
        'no_rm',
        'tanggal_lahir',
        'cara_persalinan',
        'tanggal_imd',
        'waktu_imd',
        'nama_petugas',
    ];
}
