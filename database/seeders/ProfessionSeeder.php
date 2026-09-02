<?php

namespace Database\Seeders;

use App\Models\Profession;
use Illuminate\Database\Seeder;

class ProfessionSeeder extends Seeder
{
    public function run(): void
    {
        $professions = [
            [
                'id'        => 1,
                'title_ar'  => 'مهندس برمجيات',
                'title_en'  => 'Software Engineer',
                'category'  => 'تكنولوجيا المعلومات',
                'is_active' => true,
            ],
            [
                'id'        => 2,
                'title_ar'  => 'طبيب عام',
                'title_en'  => 'General Practitioner',
                'category'  => 'الرعاية الصحية والطب',
                'is_active' => true,
            ],
            [
                'id'        => 3,
                'title_ar'  => 'محاسب قانوني',
                'title_en'  => 'Chartered Accountant',
                'category'  => 'المالية والمحاسبة',
                'is_active' => true,
            ],
            [
                'id'        => 4,
                'title_ar'  => 'متخصص تسويق رقمي',
                'title_en'  => 'Digital Marketing Specialist',
                'category'  => 'التسويق والإعلام',
                'is_active' => true,
            ],
            [
                'id'        => 5,
                'title_ar'  => 'مهندس مدني',
                'title_en'  => 'Civil Engineer',
                'category'  => 'الهندسة والبناء',
                'is_active' => true,
            ],
            [
                'id'        => 6,
                'title_ar'  => 'ممرض عام',
                'title_en'  => 'Registered Nurse',
                'category'  => 'الرعاية الصحية والطب',
                'is_active' => true,
            ],
            [
                'id'        => 7,
                'title_ar'  => 'مدير مبيعات',
                'title_en'  => 'Sales Manager',
                'category'  => 'المبيعات والتجارة',
                'is_active' => true,
            ],
            [
                'id'        => 8,
                'title_ar'  => 'مصمم جرافيك وتجربة مستخدم',
                'title_en'  => 'UI/UX & Graphic Designer',
                'category'  => 'التصميم والإبداع',
                'is_active' => true,
            ],
        ];

        foreach ($professions as $profession) {
            Profession::updateOrCreate(['id' => $profession['id']], $profession);
        }
    }
}
