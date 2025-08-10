<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imds', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nama_pasien');
            $table->string('alamat');
            $table->string('no_rm');
            $table->date('tanggal_lahir');
            $table->enum('cara_persalinan', ['SC', 'Spontan']);
            $table->date('tanggal_imd');
            $table->enum('waktu_imd', ['15 menit', '30 menit', '45 menit', '60 menit']);
            $table->string('nama_petugas');
            $table->timestamps();
            $table->softDeletes()->index(); // Add soft deletes column
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imds');
    }
};
