<?php

namespace Database\Factories;

use App\Models\Imd;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImdFactory extends Factory
{
    protected $model = Imd::class;

    public function definition(): array
    {
        return [
            'nama_pasien' => $this->faker->name(),
            'alamat' => $this->faker->address(),
            'no_rm' => $this->faker->unique()->numerify('RM####'),
            'tanggal_lahir' => $this->faker->date(),
            'cara_persalinan' => $this->faker->randomElement(['SC', 'Spontan']),
            'tanggal_imd' => $this->faker->date(),
            'waktu_imd' => $this->faker->randomElement(['15 menit', '30 menit', '45 menit', '60 menit']),
            'nama_petugas' => $this->faker->name(),
        ];
    }
}
