<?php

namespace Database\Seeders;

use App\Models\ExperienceLevel;
use Illuminate\Database\Seeder;

class ExperienceLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'id'         => 1,
                'name_ar'    => 'أقل من سنة',
                'name_en'    => 'Less than 1 year',
                'code'       => 'less_than_1',
                'min_years'  => 0,
                'max_years'  => 1,
                'sort_order' => 1,
                'is_active'  => true,
            ],
            [
                'id'         => 2,
                'name_ar'    => 'من سنة إلى 5 سنوات',
                'name_en'    => '1 to 5 years',
                'code'       => '1_to_5',
                'min_years'  => 1,
                'max_years'  => 5,
                'sort_order' => 2,
                'is_active'  => true,
            ],
            [
                'id'         => 3,
                'name_ar'    => 'من 5 إلى 8 سنوات',
                'name_en'    => '5 to 8 years',
                'code'       => '5_to_8',
                'min_years'  => 5,
                'max_years'  => 8,
                'sort_order' => 3,
                'is_active'  => true,
            ],
            [
                'id'         => 4,
                'name_ar'    => 'أكثر من 8 سنوات',
                'name_en'    => 'More than 8 years',
                'code'       => 'more_than_8',
                'min_years'  => 8,
                'max_years'  => null,
                'sort_order' => 4,
                'is_active'  => true,
            ],
        ];

        foreach ($levels as $level) {
            ExperienceLevel::updateOrCreate(['id' => $level['id']], $level);
        }
    }
}
