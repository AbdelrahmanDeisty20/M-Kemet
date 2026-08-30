<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    public function run(): void
    {
        $genders = [
            [
                'id'        => 1,
                'name_ar'   => 'ذكر',
                'name_en'   => 'Male',
                'code'      => 'male',
                'is_active' => true,
            ],
            [
                'id'        => 2,
                'name_ar'   => 'أنثى',
                'name_en'   => 'Female',
                'code'      => 'female',
                'is_active' => true,
            ],
        ];

        foreach ($genders as $gender) {
            Gender::updateOrCreate(['id' => $gender['id']], $gender);
        }
    }
}
